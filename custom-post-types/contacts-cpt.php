<?php

/**
 * =====================================================
 * Contacts Custom Post Type
 * =====================================================
 *
 * Stores contacts associated with companies.
 *
 * Author: Daniele Sousa
 * =====================================================
 */

function register_contacts_cpt() {

    $labels = array(
        'name'               => 'Contacts',
        'singular_name'      => 'Contact',
        'menu_name'          => 'Contacts',
        'add_new'            => 'Add Contact',
        'add_new_item'       => 'Add Contact',
        'edit_item'          => 'Edit Contact',
        'new_item'           => 'New Contact',
        'view_item'          => 'View Contact',
        'search_items'       => 'Search Contacts',
        'not_found'          => 'No contacts found',
        'not_found_in_trash' => 'No contacts found in trash',
    );

    $args = array(
        'labels'          => $labels,
        'public'          => true,
        'has_archive'     => true,
        'hierarchical'    => true,
        'show_ui'         => true,
        'show_in_menu'    => false,
        'supports'        => array(
            'title',
            'editor',
            'custom-fields',
            'page-attributes'
        ),
        'capability_type' => 'post',
        'rewrite'         => array(
            'slug' => 'contacts'
        ),
    );

    register_post_type(
        'contacts',
        $args
    );
}

add_action(
    'init',
    'register_contacts_cpt'
);
