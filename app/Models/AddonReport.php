<?php

namespace App\Models;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AddonReport
{
    public const POLICY_STATUS = "COALESCE(NULLIF(ba.policy_status, ''), 'pending')";

    public function transactions(string $type, string $start, string $until): Builder
    {
        // Join through the ticket: one booking code can contain multiple trips.
        $query = DB::table('tkt_booking_addons as ba')
            ->join('tkt_booking_head as bh', 'bh.booking_code', '=', 'ba.booking_code')
            ->leftJoin('tkt_passenger_pcs as pcs', 'pcs.ticket_number', '=', 'ba.ticket_number')
            ->leftJoin('tkt_booking as b', 'b.id_no', '=', 'pcs.booking_id')
            ->leftJoin('trip_route as r', 'r.id', '=', 'b.trip_route_id')
            ->where('ba.type', $type)
            ->where('bh.payment_status', 1)
            ->where('ba.created_at', '>=', $start)
            ->where('ba.created_at', '<', $until);

        if ($type === 'INSURANCE') {
            // Match admin: cancelled seats whose policy was never ordered are excluded.
            $query->whereRaw('NOT ('.self::POLICY_STATUS." = 'pending' AND COALESCE(pcs.cancel, 0) <> 0)");
        }

        return $query;
    }

    public function summary(Builder $query): object
    {
        return (clone $query)->selectRaw('COUNT(*) AS addons, COUNT(DISTINCT ba.booking_code) AS bookings,
            COALESCE(SUM(ba.fee), 0) AS fees,
            COALESCE(SUM(CASE WHEN pcs.cancel <> 0 THEN 1 ELSE 0 END), 0) AS cancelled')->first();
    }

    public function rows(Builder $query, bool $insurance): Builder
    {
        $query = (clone $query)->select([
            'ba.id', 'ba.created_at', 'ba.booking_code', 'ba.ticket_number', 'ba.fee',
            'pcs.name as passenger_name', 'pcs.cancel as ticket_cancelled',
        ])->selectRaw("COALESCE(r.name, '-') AS route_name");

        if ($insurance) {
            $query->addSelect('ba.policy_number', 'ba.reference_no')
                ->selectRaw(self::POLICY_STATUS.' AS policy_status');
        }

        return $query->orderByDesc('ba.created_at')->orderByDesc('ba.id');
    }
}
