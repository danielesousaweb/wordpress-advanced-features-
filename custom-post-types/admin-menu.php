<?php

/**
 * =====================================================
 * CRM Admin Menu
 * =====================================================
 *
 * Creates a custom CRM section in the WordPress
 * administration area.
 *
 * Author: Daniele Sousa
 * =====================================================
 */

function crm_admin_menu() {

    add_menu_page(
        'CRM',
        'CRM',
        'manage_options',
        'crm-menu',
        '',
        'dashicons-groups',
        25
    );

    add_submenu_page(
        'crm-menu',
        'Companies',
        'Companies',
        'manage_options',
        'edit.php?post_type=companies'
    );

    add_submenu_page(
        'crm-menu',
        'Contacts',
        'Contacts',
        'manage_options',
        'edit.php?post_type=contacts'
    );
}

add_action(
    'admin_menu',
    'crm_admin_menu'
);
