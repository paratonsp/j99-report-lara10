<?php

function getUserRoleInfo($email, $onlyParent = false)
{
    $result = App\Models\Menu::getUserRoleInfo(['email' => $email]);
    return $result;
}

function getMenu($role_id, $onlyParent = false)
{
    $result = strval($role_id) !== strval(1) ? App\Models\Menu::getMenuWithRole(['role_id' => $role_id]) : App\Models\SuperUser::getMenuSU();

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
    if (strval($role_id) !== strval(1) ) {
        $result = App\Models\Menu::getChildMenu([
            'parent_id' => $parent_id,
            'role_id' => $role_id
        ]);
    } else {
        $result = App\Models\SuperUser::getChildMenuSU([
            'parent_id' => $parent_id,
        ]);
    }

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
    return in_array($slug.' '.$access, $permissionData);
}

function setUserSession($email)
{
    $userRoleInfo = getUserRoleInfo($email);
    $roleAccess = getRoleAccessData($userRoleInfo->role_id);
    $menu = getMenu($userRoleInfo->role_id);

    session()->put('role_info_session', $userRoleInfo);
    session()->put('roleaccess_session', $roleAccess);
    session()->put('menu_session', $menu);

}