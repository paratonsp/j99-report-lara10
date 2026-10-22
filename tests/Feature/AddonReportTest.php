<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AddonReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
        Http::preventStrayRequests();
        DB::statement('CREATE TABLE tkt_booking_head (booking_code TEXT PRIMARY KEY, payment_status INTEGER)');
        DB::statement('CREATE TABLE tkt_booking (id_no INTEGER PRIMARY KEY, booking_code TEXT, trip_route_id INTEGER)');
        DB::statement('CREATE TABLE trip_route (id INTEGER PRIMARY KEY, name TEXT)');
        DB::statement('CREATE TABLE tkt_passenger_pcs (ticket_number TEXT PRIMARY KEY, booking_id INTEGER, name TEXT, cancel INTEGER)');
        DB::statement('CREATE TABLE tkt_booking_addons (id INTEGER PRIMARY KEY, booking_code TEXT, ticket_number TEXT,
            type TEXT, fee INTEGER, created_at TEXT, policy_status TEXT, policy_number TEXT, reference_no TEXT)');
        DB::table('tkt_booking_head')->insert([
            ['booking_code' => 'PAID', 'payment_status' => 1], ['booking_code' => 'UNPAID', 'payment_status' => 0],
        ]);
        DB::table('trip_route')->insert([['id' => 1, 'name' => 'Pergi'], ['id' => 2, 'name' => 'Pulang']]);
        DB::table('tkt_booking')->insert([
            ['id_no' => 1, 'booking_code' => 'PAID', 'trip_route_id' => 1],
            ['id_no' => 2, 'booking_code' => 'PAID', 'trip_route_id' => 2],
        ]);
        DB::table('tkt_passenger_pcs')->insert([
            ['ticket_number' => 'T1', 'booking_id' => 1, 'name' => '=SUM(1,2)', 'cancel' => 0],
            ['ticket_number' => 'T2', 'booking_id' => 2, 'name' => 'Budi', 'cancel' => 1],
        ]);
    }

    private function addon(int $id, array $overrides = []): void
    {
        DB::table('tkt_booking_addons')->insert(array_merge([
            'id' => $id, 'booking_code' => 'PAID', 'ticket_number' => 'T1', 'type' => 'INSURANCE',
            'fee' => 10000, 'created_at' => '2026-10-01 00:00:00', 'policy_status' => null,
            'policy_number' => null, 'reference_no' => null,
        ], $overrides));
    }

    private function login(): void
    {
        $this->actingAs(new User(['name' => 'Report User']))->withSession(['menu_session' => []]);
    }

    public function test_reports_and_exports_require_login(): void
    {
        foreach (['insurance', 'flexi'] as $type) {
            $this->get('/reports/'.$type)->assertRedirect('/login');
            $this->get('/reports/'.$type.'/export')->assertRedirect('/login');
        }
    }

    public function test_insurance_matches_admin_scope_without_multiplying_return_trips(): void
    {
        $this->login();
        $this->addon(1);
        $this->addon(2, ['ticket_number' => 'T2', 'policy_status' => 'issued', 'policy_number' => 'POLICY-2']);
        $this->addon(3, ['ticket_number' => 'T2', 'policy_status' => '']);
        $this->addon(4, ['booking_code' => 'UNPAID']);
        $this->addon(5, ['booking_code' => 'ORPHAN']);
        $this->addon(6, ['created_at' => '2026-11-01 00:00:00']);
        $this->addon(7, ['created_at' => '2026-09-30 23:59:59']);
        $this->addon(8, ['ticket_number' => 'MISSING', 'created_at' => '2026-10-31 23:59:59']);

        $this->get('/reports/insurance?month=2026-10')->assertOk()
            ->assertSee('POLICY-2')->assertSee('Pulang')->assertDontSee('fetchPolicy')
            ->assertViewHas('summary', fn ($s) => $s->addons == 3 && $s->bookings == 1 && $s->fees == 30000 && $s->cancelled == 1)
            ->assertViewHas('transactions', fn ($rows) => $rows->total() === 3)
            ->assertViewHas('statusCounts', fn ($counts) => $counts['pending'] == 2 && $counts['issued'] == 1);
        Http::assertNothingSent();
    }

    public function test_status_filter_applies_to_totals_list_and_export(): void
    {
        $this->login();
        $this->addon(1);
        $this->addon(2, ['policy_status' => 'issued', 'policy_number' => 'POLICY-2']);
        $this->get('/reports/insurance?month=2026-10&status=pending')->assertOk()
            ->assertDontSee('POLICY-2')
            ->assertViewHas('summary', fn ($s) => $s->addons == 1)
            ->assertViewHas('statusCounts', fn ($counts) => $counts['issued'] == 1);
        $csv = $this->get('/reports/insurance/export?month=2026-10&status=issued')->assertOk()->streamedContent();
        $this->assertStringContainsString('POLICY-2', $csv);
        $this->assertStringContainsString("'=SUM(1,2)", $csv);
        $this->assertCount(2, explode("\n", trim($csv)));
    }

    public function test_flexi_includes_cancelled_paid_tickets_and_excludes_other_addons(): void
    {
        $this->login();
        $this->addon(1, ['type' => 'REFUND_PLUS']);
        $this->addon(2, ['type' => 'REFUND_PLUS', 'ticket_number' => 'T2', 'fee' => 20000]);
        $this->addon(3);
        $this->addon(4, ['type' => 'REFUND_PLUS', 'booking_code' => 'UNPAID']);
        $this->get('/reports/flexi?month=2026-10')->assertOk()->assertSee('Flexi Tiket Report')
            ->assertDontSee('Nomor Polis')
            ->assertViewHas('summary', fn ($s) => $s->addons == 2 && $s->fees == 30000 && $s->cancelled == 1);
        $csv = $this->get('/reports/flexi/export?month=2026-10')->assertOk()->streamedContent();
        $this->assertCount(3, explode("\n", trim($csv)));
        Http::assertNothingSent();
    }

    public function test_empty_month_and_invalid_filters(): void
    {
        $this->login();
        $this->get('/reports/flexi?month=2026-10')->assertOk()->assertSee('Belum ada transaksi');
        foreach (['month=2026-13', 'month[]=2026-10', 'status=invalid', 'page=-1'] as $filter) {
            $this->getJson('/reports/insurance?'.$filter)->assertUnprocessable();
            $this->getJson('/reports/insurance/export?'.$filter)->assertUnprocessable();
        }
        $this->get('/reports/other')->assertNotFound();
    }

    public function test_pagination_and_export_keep_all_rows_with_equal_timestamps(): void
    {
        $this->login();
        for ($id = 1; $id <= 501; $id++) {
            $this->addon($id, ['type' => 'REFUND_PLUS']);
        }
        $this->get('/reports/flexi?month=2026-10')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->count() === 50 && $rows->first()->id === 501 && $rows->last()->id === 452)
            ->assertSee('month=2026-10&amp;page=2', false);
        $this->get('/reports/flexi?month=2026-10&page=2')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->first()->id === 451 && $rows->total() === 501);
        $csv = $this->get('/reports/flexi/export?month=2026-10&page=2')->assertOk()->streamedContent();
        $this->assertCount(502, explode("\n", trim($csv)));
    }
}
