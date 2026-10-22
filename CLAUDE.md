# CLAUDE.md

## Project Overview

Laravel 10 reporting application for AKAP (Antar Kota Antar Provinsi) bus transport. Uses `nwidart/laravel-modules` — the AKAP module lives in `Modules/Akap/`.

## Key Files

| File | Purpose |
|------|---------|
| `Modules/Akap/resources/views/monthlyreport.blade.php` | Monthly report page |
| `Modules/Akap/resources/views/dailyreport.blade.php` | Daily report page |
| `Modules/Akap/app/Http/Controllers/AkapMonthlyReportController.php` | Controller for monthly report |
| `Modules/Akap/app/Http/Controllers/AkapDailyReportController.php` | Controller for daily report |
| `app/Models/AkapMonthly.php` | Model with all DB query scopes |
| `Modules/Akap/routes/web.php` | AKAP web routes |

## Routes (`/akap/*`)

```
GET /akap/bulanan                — monthly report page
GET /akap/bulanan/tickets        — API: getTicket (departure month)
GET /akap/bulanan/buy            — API: getBuy (purchase month)
GET /akap/bulanan/tickets-prev   — API: getTicketPrevMonth (previous month)
GET /akap/bulanan-old            — old monthly report (AkapMonthlyController)
GET /akap/bulanan-twin           — twin monthly report

GET /akap/harian                 — daily report page (new, AJAX-based)
GET /akap/harian/tickets         — API: getTicket for date range (departure date)
GET /akap/harian/buy             — API: getBuy for date range (purchase date)
GET /akap/harian-old             — old daily report (AkapDailyController)
```

All routes are behind `auth` middleware.

## Architecture: Data Flow (both monthly and daily)

Same pattern for both reports:

1. **Page load** — controller passes only structural data via `@json()`, ticket fields start as `[]`
2. **After page load** — JS fires `Promise.all` fetches with loading overlay, populates `reportData`
3. **Render** — all values/charts built in JS after fetches complete

### Monthly (`/akap/bulanan`)
Structural data: `trip_route_grouped`, `trip_group`, `trip_assign_group`, `total_days`, `month`, `year`, `class_info`, `class_temp_off`, `class_temp_on`, `target`, `trip`
API params: `?month=&year=&trip=`

### Daily (`/akap/harian`)
Structural data: `trip_route_grouped`, `dateStart`, `dateEnd`, `trip`, `class_temp_off`, `class_temp_on`
API params: `?dateStart=&dateEnd=&trip=` (date only, no time suffix — model uses `whereDate`)
Model scopes: `AkapMonthly::getDailyTickets($dateStart, $dateEnd, $isBuy, $tripRouteIds)`
             `AkapMonthly::getDailyTemporaryOff($dateStart, $dateEnd, $tripRouteIds)`
             `AkapMonthly::getDailyTemporaryOn($dateStart, $dateEnd, $tripRouteIds)`

**Price totals:** `mainIncome`/`mainSelling` are summed over ALL tickets directly (not via route groups), so tickets on unrecognised routes are still counted. Per-route cards use route group mapping.

## JS Data Model (`reportData`)

### Monthly
```js
{
  trip_route_grouped: TripRouteGrouped[],  // route groups with .name, .route (array of route IDs)
  trip_group: number[],
  trip_assign_group: number[],
  total_days: number,
  month: string,
  year: string,
  trip: number|null,
  class_info: ClassInfo[],
  class_temp_off: TempOff[],
  class_temp_on: TempOn[],
  target: number|'-',
  getTicket: Ticket[],           // fetched: departure date in month
  getBuy: Ticket[],              // fetched: purchase date in month
  getTicketPrevMonth: Ticket[],  // fetched: departure date in previous month
}
```

### Daily
```js
{
  trip_route_grouped: TripRouteGrouped[],
  dateStart: string,   // 'YYYY-MM-DD'
  dateEnd: string,     // 'YYYY-MM-DD'
  trip: number|null,
  class_temp_off: TempOff[],   // structural (not fetched)
  class_temp_on: TempOn[],     // structural (not fetched)
  getTicket: Ticket[],         // fetched: departure date in range
  getBuy: Ticket[],            // fetched: purchase date in range
}
```

## Ticket Counting Convention

**Always count `+1` per ticket record** — do NOT use `passenger_count_tb` for occupancy/seat counts. Each record in `getTicket` represents one seat sold (one `tkt_passenger_pcs` row). Using `passenger_count_tb` causes occupancy > 100%.

Exception: price calculations still use `tp_price > 0 ? tp_price : (tb_price / passenger_count_tb)`.

## Bus Status Logic

- `status=1` — bus is **active by default**, goes off when found in `class_temp_off` for that day
- `status=0` — bus is **inactive by default**, goes on only when found in `class_temp_on` for that day

```js
function isBusOffOnDay(bus, day) {
  if (bus.status === 1) return /* in class_temp_off */;
  else return /* NOT in class_temp_on */;
}
```

## Trip Filter

When `?trip=<id>` is set:
- Controller resolves `route_x + route_y` from `trip_route_group` into `trip_route_group` array and adds it to `$reportData`
- `getAkapClassInfoList`, `getTripGroup`, `getTripAssignGroup` etc. all filter by `trip_route_group`
- API endpoints filter `getMonthlyTickets` by `whereIn('tb.trip_route_id', $tripRouteIds)`

## Chart Library

- **Chart.js v2** — use `horizontalBar` type, `xAxes`/`yAxes` array syntax for scales
- **Chart.js v3+** syntax is NOT used (no `indexAxis`, no flat `scales.x`/`scales.y` for v2 charts)
- Doughnut/bar/line charts use v3 syntax where the installed version supports it — check existing code before adding new charts

## Key JS Variables (computed after fetch)

```js
classInfoGrouped   // grouped by bus, includes days_active (minus temp_off), total_seat_month
classInfoByType    // grouped by fleet type, includes total_seat_month
ticketsByTrasDay   // { "trasId_day": count } — ticket count per bus per day
dailyMap           // { day: count } — current month daily tickets
```

## Sections in monthlyreport.blade.php

| Section | Content |
|---------|---------|
| 1 | Filters (route, month, year) + Total Income + Total Selling |
| 2 | Total Keterisian Seat (doughnut chart + percentage) |
| 3 | Occupancy By Route (bar + doughnut per route) |
| 4 | Occupancy By Class (bar + doughnut per class) |
| 5 | Daily Passenger (line chart) |
| 6 | Occupancy Rate (DataTables with fixedColumns, per bus per day) |
| 7 | Perbandingan Titik Naik/Turun (horizontal bar charts) |
| 8 | Ticketing Support (horizontal bar + pie, built from getTicket.booker) |
| 9 | Perbandingan Bulan Lalu (line chart comparing current vs prev month) |
| 10 | Jadwal Buka/Tutup Sementara (temp_on/temp_off tables) |

## Ticketing Support Categories (from booker field)

- `ybc@gmail.com` → RedBus
- `no-reply@traveloka.com` → Traveloka
- contains `kantorperwakilan` (case-insensitive) → KP
- everything else → Online