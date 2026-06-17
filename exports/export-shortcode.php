<?php
/**
 * =====================================================
 * ADVANCED SEARCH RESULTS EXPORT
 * Shortcode: [exportar_busca]
 * =====================================================
 */
 
function export_advanced_search_shortcode($atts) {
    $atts = shortcode_atts(array(
        'label'  => 'Export results',
        'format' => 'csv',
    ), $atts);
 
    if (empty($_GET) || !isset($_GET['type'])) {
        return '';
    }
 
    $params = $_GET;
    $nonce  = wp_create_nonce('exportar_busca_nonce');
 
    $formats = [
        'csv'  => ['icon' => 'fas fa-file-csv',   'label' => 'CSV'],
        'xlsx' => ['icon' => 'fas fa-file-excel',  'label' => 'Excel'],
        'json' => ['icon' => 'fas fa-code',        'label' => 'JSON'],
        'xml'  => ['icon' => 'fas fa-file-code',   'label' => 'XML'],
    ];
 
    ob_start();
    ?>
    <div class="exportar-busca-bar">
 
        <span class="exportar-busca-label">
            <?php echo esc_html($atts['label']); ?>
        </span>
 
        <?php foreach ($formats as $fmt => $cfg): ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0; display:inline;">
            <?php foreach ($params as $k => $v): ?>
                <?php if (is_array($v)): ?>
                    <?php foreach ($v as $vi): ?>
                        <input type="hidden" name="<?php echo esc_attr($k); ?>[]" value="<?php echo esc_attr($vi); ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <input type="hidden" name="<?php echo esc_attr($k); ?>" value="<?php echo esc_attr($v); ?>">
                <?php endif; ?>
            <?php endforeach; ?>
            <input type="hidden" name="action"         value="exportar_busca">
            <input type="hidden" name="formato_export" value="<?php echo esc_attr($fmt); ?>">
            <input type="hidden" name="_wpnonce"       value="<?php echo $nonce; ?>">
            <button type="submit" class="elementor-button elementor-size-sm exportar-busca-btn">
                <span class="elementor-button-content-wrapper">
                    <span class="elementor-button-icon">
                        <i aria-hidden="true" class="<?php echo esc_attr($cfg['icon']); ?>"></i>
                    </span>
                    <span class="elementor-button-text"><?php echo esc_html($cfg['label']); ?></span>
                </span>
            </button>
        </form>
        <?php endforeach; ?>
 
    </div>
 
    <style>
    .exportar-busca-bar {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        background: #f7f7f5;
        border: 0.5px solid #dddbd6;
        border-radius: 6px;
        padding: 7px 14px;
    }
    .exportar-busca-label {
        font-size: 11px;
        font-weight: 600;
        color: #888780;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
        padding-right: 8px;
        border-right: 0.5px solid #ccc;
        margin-right: 2px;
    }
    .exportar-busca-btn.elementor-button {
        padding: 5px 13px !important;
        font-size: 12px !important;
        background: #ffffff !important;
        color: #5f5e5a !important;
        border: 1px solid #d3d1c7 !important;
        border-radius: 3px !important;
        transition: background 0.15s, border-color 0.15s, color 0.15s !important;
    }
    .exportar-busca-btn.elementor-button .elementor-button-icon i {
        font-size: 11px !important;
        color: #888780 !important;
        transition: color 0.15s;
    }
    .exportar-busca-btn.elementor-button .elementor-button-text {
        font-size: 12px !important;
        font-weight: 500 !important;
    }
    .exportar-busca-btn.elementor-button:hover {
        background: #044F8B !important;
        border-color: #044F8B !important;
        color: #ffffff !important;
    }
    .exportar-busca-btn.elementor-button:hover .elementor-button-icon i {
        color: #ffffff !important;
    }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('exportar_busca', 'export_advanced_search_shortcode');
