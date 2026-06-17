<?php

/**
 * =====================================================
 * Companies Custom Post Type
 * =====================================================
 *
 * Stores company records used by the CRM.
 *
 * Author: Daniele Sousa
 * =====================================================
 */

function register_companies_cpt() {

    $labels = array(
        'name'               => 'Companies',
        'singular_name'      => 'Company',
        'menu_name'          => 'Companies',
        'add_new'            => 'Add Company',
        'add_new_item'       => 'Add Company',
        'edit_item'          => 'Edit Company',
        'new_item'           => 'New Company',
        'view_item'          => 'View Company',
        'search_items'       => 'Search Companies',
        'not_found'          => 'No companies found',
        'not_found_in_trash' => 'No companies found in trash',
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
            'slug' => 'companies'
        ),
    );

    register_post_type(
        'companies',
        $args
    );
}

add_action(
    'init',
    'register_companies_cpt'
);
