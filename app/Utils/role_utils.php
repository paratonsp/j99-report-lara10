<?php

function getUserRoleInfo($email, $onlyParent = false)
{
    $result = App\Models\Menu::getUserRoleInfo(['email' => $email]);
    return $result;
}

function getMenu($role_id, $onlyParent = false)
{
    $result = App\Models\SuperUser::getMenuSU();

    if (!$onlyParent) {
        foreach ($result as $key => $value) {
            $child = getChildMenu($value->id, $role_id);
            if (!$child->isEmpty()) {
                $value->child = $child;
            }
        }
    }

    return $result;
}

function getChildMenu($parent_id, $role_id)
{
    $result = App\Models\SuperUser::getChildMenuSU([
        'parent_id' => $parent_id,
    ]);

    return $result;
}

function getRoleAccessData($role_id)
{
    if (strval($role_id) !== strval(1)) {
        $result = App\Models\Role::getRoleAccess([
            'role_id' => $role_id
        ]);
    } else {
        $result = App\Models\SuperUser::getRoleAccess();
    }

    return $result;
}

function getSlugUrl()
{
    return request()->segment(2) ? request()->segment(2) : request()->segment(1);
}

function permissionCheck($access, $directSlug = '')
{
    $permissionData = Session('roleaccess_session');
    $slug = $directSlug ? $directSlug : getSlugUrl();
    return in_array($slug . ' ' . $access, $permissionData);
}

function setUserSession($email)
{
    $userRoleInfo = getUserRoleInfo($email);

    // A user whose role_uuid does not resolve gets no role rather than a fatal:
    // getUserRoleInfo() inner-joins v2_role, so an orphaned role_uuid returns no
    // row, and reading ->role_id off that would abort the login.
    $userRoleInfo = $userRoleInfo ?: null;
    $roleId       = $userRoleInfo->role_id ?? null;

    $roleAccess = getRoleAccessData($roleId);
    $menu = getMenu($roleId);

    session()->put('role_info_session', $userRoleInfo);
    session()->put('roleaccess_session', $roleAccess);
    session()->put('menu_session', $menu);
}
