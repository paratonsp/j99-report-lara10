# boilerplate-adminpanel-lara10

## Insurance & Flexi Tiket Reports

Authenticated report users can open **Insurance Report** (`/reports/insurance`)
and **Flexi Tiket Report** (`/reports/flexi`) from the sidebar. Both use the
existing default database connection; it must contain `tkt_booking_addons`,
`tkt_booking_head`, `tkt_passenger_pcs`, `tkt_booking`, and `trip_route`, with
the same schema as the admin application. No API credentials are needed.

Reports show paid bookings by addon purchase month, with 50 transactions per
page and a CSV export of all matching transactions. Insurance also supports a
policy status filter. Summary totals follow the selected filters; policy status
counts cover the whole month. As in admin, cancelled tickets with a pending
insurance policy are excluded; other cancelled transactions remain included.
Missing/empty insurance status is treated as pending. These pages only read
existing transaction and policy data and do not issue, retry, or refresh policies.

Run the isolated SQLite report tests with:
`vendor/bin/phpunit tests/Feature/AddonReportTest.php`.

## Admin Panel Notes


## Menu (sidebar)

The menu is separated into 2 types, there is parent and child. When you need to create a menu you have to create parent first.

## Permission

Permission is quite similiar as menu rules, when you assign child menu you have to assign parent also, so that it can be seen under the parent.

## Access name and Route sample
| Access | Menu type | Route
| :----- | :-------- | :------
| `index` | `parent` | `https://domain.com/{module}`
| `show` | `child` | `https://domain.com/{module}/{slug}` |
| `add` | `child` | `https://domain.com/{module}/{slug}/add` |
| `edit` | `child` | `https://domain.com/{module}/{slug}/edit/{uuid}` |
| `delete` | `child` | `https://domain.com/{module}/{slug}/delete/{uuid}` |

## How to make a module

```
php artisan module:make {module_name}
```
