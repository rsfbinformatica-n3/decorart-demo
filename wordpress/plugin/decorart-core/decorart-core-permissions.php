<?php
/** DecorArt Core — capabilities específicas do sistema. */
if (!defined('ABSPATH')) { exit; }

function da_register_capabilities() {
    foreach (wp_roles()->roles as $role_key => $role_data) {
        $role = get_role($role_key);
        if ($role && $role->has_cap('manage_options')) {
            $role->add_cap('access_decorart_system');
            $role->add_cap('manage_decorart_clients');
            $role->add_cap('manage_decorart_events');
        }
    }
}
add_action('init', 'da_register_capabilities', 1);

function da_can_system() { return is_user_logged_in() && current_user_can('access_decorart_system'); }
function da_can_clients() { return da_can_system() && current_user_can('manage_decorart_clients'); }
function da_can_events() { return da_can_system() && current_user_can('manage_decorart_events'); }
