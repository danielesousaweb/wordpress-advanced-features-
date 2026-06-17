<?php
/**
 * Export handler — called via admin-post.php
 */
add_action('admin_post_exportar_busca',        'handle_search_export');
add_action('admin_post_nopriv_exportar_busca', 'handle_search_export');
 
function handle_search_export() {
 
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'exportar_busca_nonce')) {
        wp_die('Access denied.', 403);
    }
 
    $format = isset($_POST['formato_export']) ? sanitize_text_field($_POST['formato_export']) : 'csv';
    $format = in_array($format, ['csv', 'xlsx', 'json', 'xml']) ? $format : 'csv';
 
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
 
    // -------------------------------------------------
    // Replicates the same field definition as the main search
    // -------------------------------------------------
    $relational_fields = [
        'agente-emp' => ['relational', 'companies'],
    ];
 
    $skip_keys = ['action', 'formato_export', '_wpnonce', 'type', 's', 'paged',
                  'orderby_field', 'order'];
 
    $meta_query = [];
    $sub_query  = [];
 
    foreach ($_POST as $key => $value) {
        $key = sanitize_text_field($key);
 
        if (in_array($key, $skip_keys))        continue;
        if (empty($value) && $value !== '0')   continue;
        if (is_array($value)) {
            $value = array_filter($value);
            if (empty($value))                 continue;
        }
        if ($value === '')                     continue;
 
        // -------------------------------------------------
        // Relational field — same logic as the main search
        // -------------------------------------------------
        $is_relational = isset($relational_fields[$key]);
        $related_post_type = $is_relational ? $relational_fields[$key][1] : '';
 
        if ($is_relational) {
            global $wpdb;
 
            $related_post_types = [$related_post_type];
            $post_types_str = "'" . implode("','", $related_post_types) . "'";
            $search_values  = is_array($value) ? $value : [$value];
            $search_values  = array_filter($search_values);
 
            if (!empty($search_values)) {
                $where_parts = [];
                foreach ($search_values as $val) {
                    $val_escaped = esc_sql($val);
                    $where_parts[] = "(pm.meta_key = '{$key}' AND pm.meta_value = '{$val_escaped}')";
                }
 
                $where_clause = implode(' OR ', $where_parts);
 
                $company_ids = $wpdb->get_col("
                    SELECT DISTINCT p.ID
                    FROM {$wpdb->posts} p
                    INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                    WHERE p.post_type IN ({$post_types_str})
                    AND ({$where_clause})
                    AND p.post_status = 'publish'
                ");
 
                if (!empty($company_ids)) {
                    $company_relation_field = 'empresa-ctt';
 
                    $sub_meta_query = ['relation' => 'OR'];
                    foreach ($company_ids as $company_id) {
                        $sub_meta_query[] = [
                            'key'     => $company_relation_field,
                            'value'   => $company_id,
                            'compare' => 'LIKE',
                        ];
                    }
                    $meta_query[] = $sub_meta_query;
 
                } else {
                    // No companies found — force an empty result
                    $meta_query[] = [
                        'key'     => 'campo_inexistente_para_forcar_vazio',
                        'value'   => 'valor_impossivel',
                        'compare' => '=',
                    ];
                }
            }
 
        // -------------------------------------------------
        // Regular field
        // -------------------------------------------------
        } else {
            $values_arr = is_array($value) ? $value : [$value];
            $sub        = ['relation' => 'OR'];
 
            foreach ($values_arr as $v) {
                $v = sanitize_text_field($v);
                $sub[] = [
                    'key'     => $key,
                    'value'   => $v,
                    'compare' => 'LIKE',
                ];
            }
            $sub_query[$key] = $sub;
        }
    }
 
    // Builds the final meta_query
    $args = [
        'post_type'      => $type,
        's'              => isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];
 
    $query_relation = ['relation' => 'AND'];
 
    if (!empty($meta_query) && empty($sub_query)) {
        $args['meta_query'] = array_merge($query_relation, $meta_query);
    } elseif (empty($meta_query) && !empty($sub_query)) {
        $args['meta_query'] = array_merge($query_relation, $sub_query);
    } elseif (!empty($meta_query) && !empty($sub_query)) {
        $args['meta_query'] = array_merge($query_relation, $meta_query, $sub_query);
    }
 
    $query = new WP_Query($args);
 
    // -------------------------------------------------
    // Fields to skip during export
    // -------------------------------------------------
    $fields_to_skip = [
        'trx_addons_post_views_count',
        'admin_form_edited',
    ];
 
    // -------------------------------------------------
    // Collects the data: post + all postmeta
    // -------------------------------------------------
    $rows    = [];
    $headers = ['ID', 'post_title', 'post_type'];
 
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post    = get_post($post_id);
 
            $row = [
                'ID'         => $post_id,
                'post_title' => $post->post_title,
                'post_type'  => $post->post_type,
            ];
 
            $all_meta = get_post_meta($post_id);
            foreach ($all_meta as $meta_key => $meta_values) {
                if (substr($meta_key, 0, 1) === '_')          continue;
                if (in_array($meta_key, $fields_to_skip))      continue;
 
                $raw_value = maybe_unserialize($meta_values[0]);
 
                if (is_array($raw_value)) {
                    $parts = [];
                    foreach ($raw_value as $item) {
                        if (is_numeric($item)) {
                            $title   = get_the_title(intval($item));
                            $parts[] = $title ?: $item;
                        } else {
                            $parts[] = $item;
                        }
                    }
                    $raw_value = implode(' | ', $parts);
                } elseif (is_numeric($raw_value) && intval($raw_value) == $raw_value) {
                    $title = get_the_title(intval($raw_value));
                    if ($title) $raw_value = $title;
                }
 
                $row[$meta_key] = $raw_value;
 
                if (!in_array($meta_key, $headers)) {
                    $headers[] = $meta_key;
                }
            }
 
            $rows[] = $row;
        }
        wp_reset_postdata();
    }
 
    foreach ($rows as &$row) {
        foreach ($headers as $h) {
            if (!isset($row[$h])) $row[$h] = '';
        }
    }
    unset($row);
 
    $file_name = 'exportacao-' . sanitize_title($type) . '-' . date('Ymd-His');
 
    switch ($format) {
        case 'csv':  export_as_csv($headers, $rows, $file_name);              break;
        case 'xlsx': export_as_xlsx($headers, $rows, $file_name);             break;
        case 'json': export_as_json($rows, $file_name);                      break;
        case 'xml':  export_as_xml($rows, $file_name, $type);                break;
    }
    exit;
}
