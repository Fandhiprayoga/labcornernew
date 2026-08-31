<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

// ---------------------------------------------------------------
// Active Group Helper Functions
// ---------------------------------------------------------------

/**
 * Get the currently active group from session.
 * If none set, defaults to the first group of the user.
 */
function activeGroup(): ?string
{
    if (! auth()->loggedIn()) {
        return null;
    }

    $session     = session();
    $activeGroup = $session->get('active_group');

    // Jika belum di-set, gunakan group pertama user
    if (empty($activeGroup)) {
        $userGroups = auth()->user()->getGroups();
        $activeGroup = ! empty($userGroups) ? $userGroups[0] : null;

        if ($activeGroup) {
            $session->set('active_group', $activeGroup);
        }
    }

    // Validasi: pastikan user masih punya group ini
    $userGroups = auth()->user()->getGroups();
    if (! in_array($activeGroup, $userGroups)) {
        $activeGroup = ! empty($userGroups) ? $userGroups[0] : null;
        $session->set('active_group', $activeGroup);
    }

    return $activeGroup;
}

/**
 * Get the active group title (human-readable).
 */
function activeGroupTitle(): string
{
    $group = activeGroup();
    if (! $group) {
        return 'No Role';
    }

    $authGroups = config('AuthGroups');

    return $authGroups->groups[$group]['title'] ?? ucfirst($group);
}

/**
 * Check if the active group has a specific permission.
 * Uses the permission matrix from AuthGroups config.
 */
function activeGroupCan(string $permission): bool
{
    $group = activeGroup();
    if (! $group) {
        return false;
    }

    $authGroups  = config('AuthGroups');
    $matrix      = $authGroups->matrix[$group] ?? [];

    foreach ($matrix as $matrixPerm) {
        // Support wildcard (e.g. 'admin.*')
        if ($matrixPerm === $permission) {
            return true;
        }

        // Wildcard check: 'users.*' matches 'users.create'
        if (str_ends_with($matrixPerm, '.*')) {
            $prefix = substr($matrixPerm, 0, -2); // remove '.*'
            if (str_starts_with($permission, $prefix . '.')) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Check if the active group matches one of the given groups.
 */
function activeGroupIs(string ...$groups): bool
{
    $active = activeGroup();

    return in_array($active, $groups);
}

// ---------------------------------------------------------------
// Notification Helper Function
// ---------------------------------------------------------------

/**
 * Ambil instance library Notification, agar bisa dipakai
 * untuk menyimpan/membaca notifikasi dari modul manapun.
 *
 * Contoh: notification()->send($userId, 'Judul', 'Pesan');
 */
function notification(): \App\Libraries\Notification
{
    return service('notification');
}

