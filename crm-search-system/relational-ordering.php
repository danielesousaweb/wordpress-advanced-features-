<?php
/**
 * =====================================================
 * FILTER FUNCTIONS - Add OUTSIDE the main function
 * Paste these functions after the closing brace of advanced_acf_search_form()
 * =====================================================
 */
function sort_join_relational_filter($join) {
    global $wpdb;

    $sort_field = $GLOBALS['sort_field'];
    $post_types_str = $GLOBALS['sort_post_types_str'];

    $unique_alias_meta = 'mt_orderby_rel';
    $unique_alias_post = 'p_orderby_rel';
    if (strpos($join, $unique_alias_meta) !== false) {
        return $join;
    }
    $join .= " LEFT JOIN {$wpdb->postmeta} AS {$unique_alias_meta} ON {$wpdb->posts}.ID = {$unique_alias_meta}.post_id";
    $join .= " AND {$unique_alias_meta}.meta_key = '{$sort_field}'";
    $join .= " LEFT JOIN {$wpdb->posts} AS {$unique_alias_post} ON (
        CASE 
            WHEN {$unique_alias_meta}.meta_value LIKE 'a:%' THEN 
                CAST(
                    TRIM(BOTH '\"' FROM
                        SUBSTRING_INDEX(
                            SUBSTRING_INDEX({$unique_alias_meta}.meta_value, '\"', 2),
                            '\"', -1
                        )
                    ) AS UNSIGNED
                )
            WHEN {$unique_alias_meta}.meta_value REGEXP '^[0-9]+$' THEN
                CAST({$unique_alias_meta}.meta_value AS UNSIGNED)
            ELSE 
                CAST(TRIM(BOTH '\"' FROM {$unique_alias_meta}.meta_value) AS UNSIGNED)
        END = {$unique_alias_post}.ID
    )";
    $join .= " AND {$unique_alias_post}.post_type IN ({$post_types_str})";
    return $join;
}
function sort_orderby_relational_filter($orderby) {
    global $wpdb;
    $order = $GLOBALS['sort_order'];
    // Primary sort by the relational field + secondary sort by contact title
    return "p_orderby_rel.post_title {$order}, {$wpdb->posts}.post_title ASC";
}
function sort_join_normal_filter($join) {
    global $wpdb;

    $sort_field = $GLOBALS['sort_field'];
    $alias = 'mt_ord_' . str_replace('-', '_', $sort_field);
    if (strpos($join, $alias) !== false) {
        return $join;
    }
    $join .= " LEFT JOIN {$wpdb->postmeta} AS {$alias} ON {$wpdb->posts}.ID = {$alias}.post_id AND {$alias}.meta_key = '{$sort_field}'";
    return $join;
}
function sort_orderby_normal_filter($orderby) {
    global $wpdb;
    $sort_field = $GLOBALS['sort_field'];
    $order = $GLOBALS['sort_order'];

    $alias = 'mt_ord_' . str_replace('-', '_', $sort_field);
    // Primary sort by the selected field + secondary sort by title
    return "{$alias}.meta_value {$order}, {$wpdb->posts}.post_title ASC";
}
function sort_groupby_filter($groupby) {
    global $wpdb;
    if (empty($groupby)) {
        return "{$wpdb->posts}.ID";
    }
    return $groupby;
}
