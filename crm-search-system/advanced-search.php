<?php
function advanced_acf_search_form($post_type) {
		// Defines the ACF fields for each post type
  		$acf_fields = array(
			'contacts' 	=> array('email-ctt' => 0, 'sexo-ctt' => 0, 'endereco-ctt' => 0, 'cidade-ctt' => 0, 'pais-ctt' => 0, 'estado-ctt' => 0, 'forum-ctt' => array('rel_con',0), 'waf-ctt' => array('rel_con',0), 'palestrante-waf-ctt' => array('rel_con',0), 'pesquisa-ctt' => array('rel_con',0), 'academy-ctt' => array('rel_con',0), 'anos-waf-ctt' => array('rel_con',1), 'anos-palestr-ctt' => array('rel_con',1), 'tipo-pesquisa-ctt' => array('rel_con',1), 'quais-webinares-ctt' => array('rel_con',1), 'empresa-ctt' => 1, 'departamento-ctt' => 1, 'cargo-ctt' => 1, 'agente-emp' => array('relational', 'companies', 1)),

    		'companies' => array('setor-emp' => 0, 'endereco-emp' => 0, 'cidade-emp' => 0, 'pais-emp' => 0, 'e-mail-emp' => 0, 'estado-emp' => 0, 'representante-emp' => 0, 'grupo-emp' => 1, 'relac-emp' => 1, 'produtos-emp' => 1,'responsavel-emp' => 1, 'sw' => array('soluc-emp',1), 'hw' => array('soluc-emp',1), 'servicos' => array('soluc-emp',1), 'agente-emp' => 1, 'tipo_ger' => array('geracao-emp',1)),
  		);

		// Defines which fields will be available for sorting in each post_type
		// For fields that store IDs of other posts, use: array('label' => '...', 'post_type' => '...')
		$sorting_options = array(
			'contacts' => array(
				'post_title' => 'Name',
				'email-ctt' => 'Email',
				'cargo-ctt' => 'Job Title',
				'empresa-ctt' => array('label' => 'Company', 'post_type' => 'companies'),
				'departamento-ctt' => 'Department',
			),
			'companies' => array(
				'post_title' => 'Name',
				'setor-emp' => 'Sector',
				'agente-emp' => 'Agent',
				'grupo-emp' => array('label' => 'Business Group', 'post_type' => 'grupos_empresariais'),
				'responsavel-emp' => array('label' => 'Owner', 'post_type' => 'funcionarios')
			),
			'funcionarios' => array(
				'post_title' => 'Name',
				'email_funcionario' => 'Email',
				'departamento_funcionario' => 'Department'
			),
			'precontatos' => array(
				'post_title' => 'Name',
				'e-mail-pct' => 'Email',
				'cargo-pct' => 'Job Title',
				'empresa-pct' => array('label' => 'Company', 'post_type' => 'companies')
			),
			'grupos_empresariais' => array(
				'post_title' => 'Name'
			)
		);

  		// Checks whether the current post type has ACF fields defined
  		if (isset($acf_fields[$post_type])) {
    		$fields = $acf_fields[$post_type];
			$sort_options_for_type = isset($sorting_options[$post_type]) ? $sorting_options[$post_type] : array('post_title' => 'Name');

    		// Displays the search form
			echo '<div id="form-busca-avancada" style="display:block; width:100%;">';
			echo '<p style="font-size:12px; width:100%; background:#ECEAEA; padding:10px 20px; border-radius:20px; border:1px solid #dddddd;"><i aria-hidden="true" class="fas fa-info-circle"></i> Hold <strong>Ctrl</strong> (or <strong>Cmd</strong> on Mac) to select multiple items. Click outside the list to deselect.</p>';

    		echo '<form role="search" method="get" id="searchform form-busca-avancada" action="#resultados-busca" class="searchform" action="' . esc_url(home_url('/'.$post_type)) . '">';
    		echo '<input type="hidden" name="type" value="' . $post_type . '" />';
			echo '<input type="hidden" name="paged" value="1" />';

			echo '<div class="field-search" style="width:25%; float:left; padding:5px 10px 5px 10px;">';
			echo '<div class="linha-label"><span class="label-text">Name</span></div>';
    		echo '<input type="text" name="s" value="' . (isset($_GET['s']) ? $_GET['s'] : '') . '" placeholder="Name:" style="width:100%;">';
    		echo '</div>';

    		foreach ($fields as $field_key => $field_type) {
				// Checks if it is a relational field
				$is_relational = is_array($field_type) && $field_type[0] === 'relational';

				if($is_relational) {
					// For relational fields: array('relational', 'related_post_type', select_type)
					$related_post_type = $field_type[1];
					$select_type = $field_type[2];
					$meta_key = $field_key;
					$field = acf_get_field($field_key);
				} else {
					$field = acf_get_field($field_key);
					$meta_key = is_array($field_type) ? $field_type[0] . '_' . $field_key : $field_key;
					if(is_array($field_type)){ $field_type = $field_type[1]; }
				}

				$label = isset($field['label']) ? $field['label'] : $field_key;

				if(isset($_GET[$meta_key])&&$_GET[$meta_key]!=''){
					echo '<div class="field-search campo-pesquisado" style="width:25%; float:left; padding:5px 10px 5px 10px;">';
				}
				else{
					echo '<div class="field-search" style="width:25%; float:left; padding:5px 10px 5px 10px;">';
				}
				echo '<div class="linha-label tooltip-wrapper" ';

				if(($is_relational && $select_type == 1) || $field_type == 1){
					echo 'data-tooltip="Hold Ctrl (or Cmd on Mac) to select multiple values."';
				}
				echo '>';
				echo    '<label class ="label-text" for="' . esc_attr($meta_key) . '">' . esc_html($label) . '</label>';
				if(($is_relational && $select_type == 1) || $field_type == 1){
					echo '<span class="limpar-selecao" onclick="limparSelecao(\'' . esc_attr($meta_key) . '\')">X Clear</span>';
				}
				echo '</div>';


				// If it's a relational field, fetch the ACF field options
				if($is_relational) {
					// Fetch the ACF field to get the options/choices
					$field_acf = acf_get_field($field_key);

					if($select_type == 0){
						echo '<select name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '">';
					}
					else if($select_type == 1){
						echo '<select name="' . esc_attr($meta_key) . '[]" id="' . esc_attr($meta_key) . '" size="6" class="select-multiple-busca" multiple>';
					}

					echo '<option value="" ';
					if(!isset($_GET[$meta_key]) || empty($_GET[$meta_key]) || isset($_GET[$meta_key]) == ''){
						echo ' selected ';
					}
					echo ' style="background:#FAFAFA;"><b>' . esc_html($label) . '</b></option>';

					// If the field has defined choices (select, radio, checkbox)
					if(isset($field_acf['choices']) && !empty($field_acf['choices'])) {
						foreach ($field_acf['choices'] as $k => $v) {
							echo '<option value="' . esc_attr($k) . '"';
							if(isset($_GET[$meta_key]) && is_array($_GET[$meta_key])){
								if (in_array($k, $_GET[$meta_key])){
									echo ' SELECTED ';
								}
							}else{
								if (isset($_GET[$meta_key]) && $_GET[$meta_key] == $k) {
									echo ' SELECTED ';
								}
							}
							echo '>' . esc_html($v) . '</option>';
						}
					}
					// If there are no choices, fetch unique values from the database (fallback)
					else {
						global $wpdb;

						$related_post_types = array($related_post_type);
						$post_types_str = "'" . implode("','", $related_post_types) . "'";

						$available_values = $wpdb->get_results("
							SELECT DISTINCT meta_value 
							FROM {$wpdb->postmeta} pm
							INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
							WHERE p.post_type IN ({$post_types_str})
							AND pm.meta_key = '{$field_key}'
							AND pm.meta_value != ''
							ORDER BY pm.meta_value ASC
						");

						foreach ($available_values as $value_obj) {
							$value = $value_obj->meta_value;
							echo '<option value="' . esc_attr($value) . '"';
							if(isset($_GET[$meta_key]) && is_array($_GET[$meta_key])){
								if (in_array($value, $_GET[$meta_key])){
									echo ' SELECTED ';
								}
							}else{
								if (isset($_GET[$meta_key]) && $_GET[$meta_key] == $value) {
									echo ' SELECTED ';
								}
							}
							echo '>' . esc_html($value) . '</option>';
						}
					}

					echo '</select>';
				}
				// If the field is of type "Post Object"
    			elseif ($field['type'] === 'post_object' || $field['type'] === 'relationship') {
        			$post_type_field = $field['post_type'] ?? 'post';
        			$post_status 		= $field['post_status'] ?? 'publish';

        			$args_select = [
            			'post_type'      => $post_type_field,
            			'posts_per_page' => -1,
            			'orderby'        => 'title',
            			'order'          => 'ASC',
            			'post_status'    => $post_status,
        			];

					// Getting the taxonomy associated with the posts and filtering
        			$taxonomies = get_object_taxonomies($post_type_field, 'names'); // Gets the CPT's taxonomies
					$tax_query  = [];

					if(isset($taxonomies) && !empty($taxonomies)){
						foreach ($field['taxonomy'] as $taxonomy) {
							$term = explode(':',$taxonomy);

            				$tax_query[] = [
                				'taxonomy' => $term[0],
                				'field'    => 'slug',
                				'terms'    => $term[1],
            				];
        				}
					}



        			// If there are filtered terms, apply them to WP_Query
        			if (!empty($tax_query)) {
            			$args_select['tax_query'] = ['relation' => 'AND'] + $tax_query;
        			}

        			$related_posts = get_posts($args_select);

					if($field_type == 0){
						echo '<select name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '">';
					}
					else if($field_type == 1){
						echo '<select name="' . esc_attr($meta_key) . '[]" id="' . esc_attr($meta_key) . '" size="6" class="select-multiple-busca" multiple>';
					}
        			echo '<option value="" ';
					if(!isset($_GET[$meta_key]) || empty($_GET[$meta_key]) || isset($_GET[$meta_key]) == ''){
						echo ' selected ';
					}
					echo ' style="background:#FAFAFA;"><b>' . esc_html($label) . '</b></option>';

        			foreach ($related_posts as $post) {
            			echo '<option value="' . esc_attr($post->ID) . '"';
						if(isset($_GET[$meta_key]) && is_array($_GET[$meta_key])){
							if (in_array($post->ID, $_GET[$meta_key])){
                				echo ' SELECTED ';
            				}
						}else{
							if (isset($_GET[$meta_key]) === $post->ID) {
                				echo ' SELECTED ';
            				}
						}
            			echo '>' . esc_html($post->post_title) . '</option>';
        			}

        			echo '</select>';
    			}
    			// If it's a regular select field
    			elseif (isset($field['choices'])) {
					if($field_type==0){
						echo '<select name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" teste>';
					}
					if($field_type==1){
						echo '<select name="' . esc_attr($meta_key) . '[]" id="' . esc_attr($meta_key) . '" size="6" class="select-multiple-busca" multiple>';
					}
        			echo '<option value=""';
					if(!isset($_GET[$meta_key]) || empty($_GET[$meta_key]) || isset($_GET[$meta_key]) == ''){
						echo ' selected ';
					}
					echo ' style="background:#FAFAFA;"><b>' . esc_html($label) . '</b></option>';

        			foreach ($field['choices'] as $k => $v) {
            			echo '<option value="' . esc_attr($k) . '"';
						if(isset($_GET[$meta_key]) && is_array($_GET[$meta_key])){
							if (in_array($k, $_GET[$meta_key])) {
                				echo ' SELECTED ';
            				}
						}else{
							if (isset($_GET[$meta_key]) && $_GET[$meta_key] == $k) {
                				echo ' SELECTED ';
            				}
						}

            			echo '>' . esc_html($v) . '</option>';
        			}
        			echo '</select>';
    			}elseif($field['type'] == 'date'){
					echo '<input type="datetime" name="' . esc_attr($meta_key) . '" value="' . (isset($_GET[$meta_key]) ? esc_attr($_GET[$meta_key]) : '') . '" placeholder="' . esc_html($label) . ':" style="width:100%;">';
				} else {
        			echo '<input type="text" name="' . esc_attr($meta_key) . '" value="' . (isset($_GET[$meta_key]) ? esc_attr($_GET[$meta_key]) : '') . '" placeholder="' . esc_html($label) . ':" style="width:100%;">';
    			}

				echo '</div>';
			}

			// Sorting area - aligned to the right with shading
			echo '<div id="area-ordenacao" style="float:left; width:100%; margin-top:20px; padding:15px; background:#f9f9f9; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">';
			echo '<div style="display:flex; gap:15px; align-items:flex-end; justify-content:flex-end; flex-wrap:wrap;">';

			// "Sort by" select
			echo '<div style="display:flex; flex-direction:column;">';
			echo '<label style="font-size:12px; font-weight:600; margin-bottom:5px; color:#333;">Sort by:</label>';
			echo '<select name="orderby_field" id="orderby_field" style="padding:8px 12px; border:1px solid #ddd; border-radius:4px; min-width:200px;">';
			echo '<option value="">Default (Name)</option>';

			foreach ($sort_options_for_type as $sort_field_key => $sort_field_config) {
				$sort_field_label = is_array($sort_field_config) ? $sort_field_config['label'] : $sort_field_config;
				$selected = (isset($_GET['orderby_field']) && $_GET['orderby_field'] == $sort_field_key) ? ' selected' : '';
				echo '<option value="' . esc_attr($sort_field_key) . '"' . $selected . '>' . esc_html($sort_field_label) . '</option>';
			}

			echo '</select>';
			echo '</div>';

			// "Order" select
			echo '<div style="display:flex; flex-direction:column;">';
			echo '<label style="font-size:12px; font-weight:600; margin-bottom:5px; color:#333;">Order:</label>';
			echo '<select name="order" id="order" style="padding:8px 12px; border:1px solid #ddd; border-radius:4px; min-width:150px;">';
			$order_asc_selected = (!isset($_GET['order']) || $_GET['order'] == 'ASC') ? ' selected' : '';
			$order_desc_selected = (isset($_GET['order']) && $_GET['order'] == 'DESC') ? ' selected' : '';
			echo '<option value="ASC"' . $order_asc_selected . '>Ascending (A-Z)</option>';
			echo '<option value="DESC"' . $order_desc_selected . '>Descending (Z-A)</option>';
			echo '</select>';
			echo '</div>';

			// INLINE button (appears once the sorting area is reached)
			echo '<div id="btn-busca-inline-container" style="display:none;">';
			echo '<input type="submit" value="🔍 SEARCH" class="bt-busca-avancada bt-busca-inline" style="padding:8px 30px; color:white; border:none; border-radius:8px; font-size:16px; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(4,79,139,0.3); transition:all 0.3s ease; height:42px;">';
			echo '</div>';

			echo '</div>'; // Closes flex container
			echo '</div>'; // Closes sorting area

			// FIXED button (appears while the sorting area has not been reached)
			echo '<div id="btn-busca-fixo-container" style="position:fixed; bottom:0; left:0; right:0; z-index:999; backdrop-filter:blur(8px); background:rgba(255,255,255,0.85); padding:15px 0; transition:all 0.3s ease;">';
			echo '<div style="max-width:1200px; margin:0 auto; padding:0 10px;">';
			echo '<input type="submit" value="🔍 SEARCH" class="bt-busca-avancada bt-busca-avancada-fixo" style="width:70%; max-width:600px; margin:0 auto; display:block; padding:12px 30px; color:white; border:none; border-radius:8px; font-size:16px; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(4,79,139,0.3); transition:all 0.3s ease;">';
			echo '</div>';
			echo '</div>';

			echo '</form>';
			echo '</div>';

			// CSS and JavaScript
			echo '<style>
			option{padding:10px 0px 10px 30px; border-bottom:1px solid #fbfbfb;} 
			select option[value=""]{font-weight:700;} 
			select option:hover{background:#fafafa;}

			/* Pulse animation for the fixed button */
			@keyframes pulse {
				0%, 100% {
					transform: scale(1);
					box-shadow: 0 4px 15px rgba(4,79,139,0.3);
				}
				50% {
					transform: scale(1.02);
					box-shadow: 0 6px 25px rgba(4,79,139,0.5);
				}
			}

			.bt-busca-avancada-fixo {
				animation: pulse 2.5s ease-in-out infinite;
			}

			.bt-busca-avancada-fixo:hover,
			.bt-busca-inline:hover {
				background: linear-gradient(135deg, #0369B8 0%, #055a9e 100%) !important;
				transform: scale(1.03) !important;
				box-shadow: 0 6px 25px rgba(4,79,139,0.5) !important;
				animation: none;
			}

			.bt-busca-avancada-fixo:active,
			.bt-busca-inline:active {
				transform: scale(0.98) !important;
			}

			/* Smooth transition when hiding/showing */
			#btn-busca-fixo-container {
				transition: opacity 0.3s ease, transform 0.3s ease;
			}

			#btn-busca-fixo-container.hidden {
				opacity: 0;
				transform: translateY(100%);
				pointer-events: none;
			}

			#btn-busca-inline-container {
				transition: opacity 0.3s ease;
			}
			</style>';

			echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				var sortingArea = document.getElementById("area-ordenacao");
				var fixedBtn = document.getElementById("btn-busca-fixo-container");
				var inlineBtn = document.getElementById("btn-busca-inline-container");

				if (!sortingArea || !fixedBtn || !inlineBtn) return;

				function checkPosition() {
					var rect = sortingArea.getBoundingClientRect();
					var windowHeight = window.innerHeight;

					var areaVisible = rect.top < (windowHeight * 0.85);

					if (areaVisible) {
						fixedBtn.classList.add("hidden");
						inlineBtn.style.display = "block";
					} else {
						fixedBtn.classList.remove("hidden");
						inlineBtn.style.display = "none";
					}
				}

				window.addEventListener("scroll", checkPosition, { passive: true });
				window.addEventListener("resize", checkPosition, { passive: true });
				checkPosition();
			});
			</script>';

    		// Processes the search
    		if (!empty($_GET) && isset($_GET['type'])) {
      			$type = $_GET['type'];

      			$args = array(
        			'post_type'      => $type,
        			's'              => isset($_GET['s']) ? $_GET['s'] : '',
        			'posts_per_page' => 50,
        			'paged'          => get_query_var('paged') ? get_query_var('paged') : 1,
    			);

				// SORTING LOGIC WITH SUPPORT FOR RELATIONAL FIELDS
				if (isset($_GET['orderby_field']) && !empty($_GET['orderby_field'])) {
					$sort_field = $_GET['orderby_field'];
					$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

					if ($sort_field == 'post_title') {
						$args['orderby'] = 'title';
						$args['order'] = $order;
					} else {
						$field_config = isset($sort_options_for_type[$sort_field]) ? $sort_options_for_type[$sort_field] : null;
						$is_relational_id_field = is_array($field_config) && isset($field_config['post_type']);

						if ($is_relational_id_field) {
							global $wpdb;

							$related_post_types = is_array($field_config['post_type']) ? $field_config['post_type'] : array($field_config['post_type']);
							$post_types_str = "'" . implode("','", $related_post_types) . "'";

							$unique_alias_meta = 'mt_orderby_rel';
							$unique_alias_post = 'p_orderby_rel';

							// Stores variables in global scope for use in the filters
							$GLOBALS['ordenacao_campo'] = $sort_field;
							$GLOBALS['ordenacao_post_types_str'] = $post_types_str;
							$GLOBALS['ordenacao_ordem'] = $order;

							add_filter('posts_join', 'filtro_ordenacao_join_relacional', 10, 1);
							add_filter('posts_orderby', 'filtro_ordenacao_orderby_relacional', 10, 1);
							add_filter('posts_groupby', 'filtro_ordenacao_groupby', 10, 1);

						} else {
							$args['meta_key'] = $sort_field;
							$args['orderby'] = 'meta_value';
							$args['order'] = $order;
						}
					}
				} else {
					$args['orderby'] = 'title';
					$args['order'] = 'ASC';
				}


    			foreach ($fields as $field_key => $field_type) {
					// Checks if it is a relational field
					$is_relational = is_array($field_type) && $field_type[0] === 'relational';

					if($is_relational) {
						$related_post_type = $field_type[1];
						$meta_key = $field_key;
					} else {
						$meta_key = is_array($field_type) ? $field_type[0] . '_' . $field_key : $field_key;
					}

        			if (isset($_GET[$meta_key]) && !empty($_GET[$meta_key]) && $_GET[$meta_key] != "") {
            			$value = $_GET[$meta_key];

						// If it's a relational field (reverse lookup)
						if($is_relational) {
							global $wpdb;

							$related_post_types = array($related_post_type);
							$post_types_str = "'" . implode("','", $related_post_types) . "'";

							// Prepares the values for the search (can be array or string)
							$search_values = is_array($value) ? $value : array($value);
							$search_values = array_filter($search_values); // Removes empty values

							if(!empty($search_values)) {
								// Builds the query to find IDs of companies with the selected agents
								$where_parts = array();

								foreach($search_values as $val) {
									$val_escaped = esc_sql($val);
									$where_parts[] = "(pm.meta_key = '{$field_key}' AND pm.meta_value = '{$val_escaped}')";
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

								if(!empty($company_ids)) {
									// Now look up contacts that have these companies
									$company_relation_field = 'empresa-ctt';

									// Creates a sub-query to find contacts related to these companies
									$sub_meta_query = array('relation' => 'OR');
									foreach($company_ids as $company_id) {
										$sub_meta_query[] = array(
											'key'     => $company_relation_field,
											'value'   => 's:5:"' . $company_id . '"',
											'compare' => 'LIKE'
										);
									}

									if(isset($meta_query)) {
										$meta_query[] = $sub_meta_query;
									} else {
										$meta_query = array($sub_meta_query);
									}
								} else {
									// If no companies were found with that agent, force an empty result
									$meta_query[] = array(
										'key'     => 'campo_inexistente_para_forcar_vazio',
										'value'   => 'valor_impossivel',
										'compare' => '='
									);
								}
							}
						}
						// Regular search (non-relational)
						else {
							if (is_array($_GET[$meta_key]) && !empty(array_filter($_GET[$meta_key]))) {
								$_GET[$meta_key] = array_filter($_GET[$meta_key]);
								$sub_meta_query = array('relation' => 'OR');

								foreach($_GET[$meta_key] as $value){
									$sub_meta_query[] = array(
										'key'		=> $meta_key,
										'value'		=> $value,
										'compare'	=> 'LIKE'
									);
								}

								if(isset($meta_query)) {
									$meta_query[] = $sub_meta_query;
								} else {
									$meta_query = array($sub_meta_query);
								}
							}

							if(!is_array($_GET[$meta_key]) && $_GET[$meta_key] !== ""){
								$meta_query[] = array(
									'key'     => $meta_key,
									'value'   => $value,
									'compare' => 'LIKE'
								);
							}
						}
        			}
    			}

				if(isset($meta_query)){
					$query_relation = array('relation' => 'AND');
					$args['meta_query'] = array_merge($query_relation, $meta_query);
				}


				$query = new WP_Query($args);

				// Removes the filters after the query so they don't affect other queries
				remove_all_filters('posts_join');
				remove_all_filters('posts_orderby');
				remove_all_filters('posts_groupby');

      			echo '<div id="resultados-busca" style="display:block; width:100%; float:left; margin-top:40px; padding-bottom:100px;">';
      			echo '<p style="width:100%; text-align:right;">Results found: <span style="color:#044F8B; font-weight:600;">' . $query->found_posts . '</span></p>';

      			// Displays the search results
      			if ($query->have_posts()) {
        			while ($query->have_posts()) {
          			$query->the_post();
          			$post_id = get_the_ID();

          			if($_GET['type']=='companies'){ echo do_shortcode('[elementor-template id="12227"]');}
          			elseif($_GET['type']=='contacts'){ echo do_shortcode('[elementor-template id="12219"]'); }
        		}

        		// Pagination
        		$big = 999999999; // Large number to ensure the page number is unique
        		echo paginate_links(array(
          			'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
          			'format' => '?paged=%#%',
          			'current' => max(1, get_query_var('paged')),
          			'total' => $query->max_num_pages
        		));

			} else {
    			echo '<p>No results found.</p>';
    		}
      		echo '</div>';
      		wp_reset_postdata();
		}
    }
}
