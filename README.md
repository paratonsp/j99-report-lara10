# boilerplate-adminpanel-lara10

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
