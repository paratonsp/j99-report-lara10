<?php

namespace App\Http\Controllers;

use App\Models\AddonReport;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AddonReportController extends Controller
{
    public function index(Request $request, AddonReport $report)
    {
        return $this->render($request, $report, false);
    }

    public function export(Request $request, AddonReport $report)
    {
        return $this->render($request, $report, true);
    }

    private function render(Request $request, AddonReport $report, bool $export)
    {
        $insurance = $request->route('report') === 'insurance';
        $statuses = ['pending', 'submitted', 'issued', 'stalled', 'failed'];
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m', 'regex:/\A[1-9][0-9]{3}-(0[1-9]|1[0-2])\z/'],
            'status' => ['nullable', Rule::in($insurance ? $statuses : [])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $month = $request->input('month') ?: now()->format('Y-m');
        $start = CarbonImmutable::createFromFormat('!Y-m', $month);
        $query = $report->transactions($insurance ? 'INSURANCE' : 'REFUND_PLUS',
            $start->format('Y-m-d'), $start->addMonth()->format('Y-m-d'));

        $statusCounts = $insurance
            ? (clone $query)->selectRaw(AddonReport::POLICY_STATUS.' AS policy_status, COUNT(*) AS total')
                ->groupByRaw(AddonReport::POLICY_STATUS)->pluck('total', 'policy_status')
            : collect();
        $status = $request->input('status');
        if ($insurance && $status) {
            $query->whereRaw(AddonReport::POLICY_STATUS.' = ?', [$status]);
        }

        $rows = $report->rows($query, $insurance);
        $title = $insurance ? 'Insurance Report' : 'Flexi Tiket Report';
        if ($export) {
            return response()->streamDownload(function () use ($rows, $insurance) {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");
                $headers = ['Tanggal Pembelian', 'Booking', 'Tiket', 'Penumpang', 'Rute', 'Status Tiket', 'Biaya (IDR)'];
                if ($insurance) {
                    $headers = array_merge($headers, ['Status Polis', 'Nomor Polis', 'Referensi']);
                }
                fputcsv($output, $headers, ',', '"', '');
                foreach ($rows->lazy(500) as $row) {
                    $values = [$row->created_at, $row->booking_code, $row->ticket_number,
                        $row->passenger_name, $row->route_name,
                        $row->ticket_cancelled === null ? 'Tidak diketahui' : ($row->ticket_cancelled != 0 ? 'Batal' : 'Aktif'), $row->fee];
                    if ($insurance) {
                        $values = array_merge($values, [$row->policy_status, $row->policy_number, $row->reference_no]);
                    }
                    // Passenger names and identifiers must not become spreadsheet formulas.
                    $values = array_map(static function ($value) {
                        $value = (string) $value;

                        return preg_match('/^[\s]*[=+@-]/u', $value) ? "'".$value : $value;
                    }, $values);
                    fputcsv($output, $values, ',', '"', '');
                }
                fclose($output);
            }, ($insurance ? 'insurance' : 'flexi-tiket').'-'.$month.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('reports.addons', [
            'title' => $title, 'insurance' => $insurance, 'month' => $month,
            'statuses' => $statuses, 'statusCounts' => $statusCounts, 'status' => $status,
            'summary' => $report->summary($query),
            'transactions' => $rows->paginate(50)->withQueryString(),
            'reportType' => $request->route('report'),
        ]);
    }
}
