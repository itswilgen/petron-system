<?php

define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');

function canAccessSuperAdminArea($role) {
    return $role === ROLE_SUPER_ADMIN;
}

function canAccessAdminArea($role) {
    return $role === ROLE_ADMIN;
}

function canAccessStaffArea($role) {
    return $role === ROLE_ADMIN || $role === ROLE_STAFF;
}

function allowedCreationRolesFor($role) {
    if ($role === ROLE_SUPER_ADMIN) {
        return [ROLE_ADMIN, ROLE_STAFF];
    }

    if ($role === ROLE_ADMIN) {
        return [ROLE_STAFF];
    }

    return [];
}
