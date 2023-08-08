<?php

/**
 * Class tdb_module_taxonomies - shortcode for cloud template modules (renders post title)
 */
class tdb_module_taxonomies extends tdb_module_template_part {

    public function get_custom_css() {

		$style_selector = self::$style_selector;
		$style_atts_uid = self::$style_atts_uid;
		

        $compiled_css = '';

        $raw_css = "<style>
		
			/* @style_general_tdb_module_taxonomies */
			.tdb_module_taxonomies {
				display: flex;
				flex-wrap: wrap;
				position: relative;
				margin: 0;
			}
			.tdb_module_taxonomies .tdb-module-term {
				padding: 3px 6px 4px;
				background-color: #222222;
				font-family: 'Open Sans', 'Open Sans Regular', sans-serif;
				font-size: 10px;
				font-weight: 600;
				line-height: 1;
				color: #fff;
				transition: all 0.2s ease;
				-webkit-transition: all 0.2s ease;
			}
			.tdb_module_taxonomies .tdb-module-term:hover {
				background-color: #4db2ec;
			}
			.tdb_module_taxonomies .tdb-module-term-sep {
				align-self: center;
				position: relative;
				font-size: 14px;
				color: red;
			}
			.tdb_module_taxonomies .tdb-module-term-sep svg {
				display: block;
				width: 1em;
				height: auto;
			}
			.tdb_module_taxonomies .tdb-module-term-sep svg,
			.tdb_module_taxonomies .tdb-module-term-sep svg * {
				fill: currentColor;
			}



			/* @tdb_mts_align_horiz_$style_atts_uid */
			.$style_selector {
				justify-content: @tdb_mts_align_horiz_$style_atts_uid;
			}

			/* @tdb_mts_space_$style_atts_uid */
			.$style_selector {
				gap: @tdb_mts_space_$style_atts_uid;
			}

			/* @tdb_mts_padding_$style_atts_uid */
			.$style_selector .tdb-module-term {
				padding: @tdb_mts_padding_$style_atts_uid;
			}
			/* @tdb_mts_all_border_$style_atts_uid */
			.$style_selector .tdb-module-term {
				border: @tdb_mts_all_border_$style_atts_uid @tdb_mts_all_border_style_$style_atts_uid @tdb_mts_all_border_color_$style_atts_uid;
			}
			/* @tdb_mts_radius_$style_atts_uid */
			.$style_selector .tdb-module-term {
				border-radius: @tdb_mts_radius_$style_atts_uid;
			}

			/* @tdb_mts_ico_size_$style_atts_uid */
			.$style_selector .tdb-module-term-sep {
				font-size: @tdb_mts_ico_size_$style_atts_uid;
			}
			/* @tdb_mts_ico_align_$style_atts_uid */
			.$style_selector .tdb-module-term-sep {
				top: @tdb_mts_ico_align_$style_atts_uid;
			}



			/* @tdb_mts_bg_$style_atts_uid */
			.$style_selector .tdb-module-term {
				background-color: @tdb_mts_bg_$style_atts_uid;
			}
			/* @tdb_mts_bg_h_$style_atts_uid */
			.$style_selector .tdb-module-term:hover {
				background-color: @tdb_mts_bg_h_$style_atts_uid;
			}
			/* @tdb_mts_color_$style_atts_uid */
			.$style_selector .tdb-module-term {
				color: @tdb_mts_color_$style_atts_uid;
			}
			/* @tdb_mts_color_h_$style_atts_uid */
			.$style_selector .tdb-module-term:hover {
				color: @tdb_mts_color_h_$style_atts_uid;
			}
			/* @tdb_mts_border_color_h_$style_atts_uid */
			.$style_selector .tdb-module-term:hover {
				border-color: @tdb_mts_border_color_h_$style_atts_uid;
			}

			/* @tdb_mts_ico_color_$style_atts_uid */
			.$style_selector .tdb-module-term-sep {
				color: @tdb_mts_ico_color_$style_atts_uid;
			}



			/* @tdb_mts_f_txt_$style_atts_uid */
			.$style_selector .tdb-module-term {
				@tdb_mts_f_txt_$style_atts_uid
			}
		
		</style>";

        $td_css_res_compiler = new td_css_res_compiler( $raw_css );
        $td_css_res_compiler->load_settings( __CLASS__ . '::cssMedia', $this->get_all_atts() );

        $compiled_css .= $td_css_res_compiler->compile_css();
        return $compiled_css;

    }

    static function cssMedia( $res_ctx ) {

		$style_atts_uid = self::$style_atts_uid;




		/* --
		-- GENERAL
		-- */
		$res_ctx->load_settings_raw( 'style_general_tdb_module_taxonomies', 1 );
        if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
            $res_ctx->load_settings_raw( 'style_general_tdb_module_taxonomies_composer', 1 );
        }




		/* --
		-- AUTHOR NAME
		-- */
		/* -- Layout -- */
		// Horizontal align
		$align_horiz = $res_ctx->get_shortcode_att( 'align_horiz' );
		switch( $align_horiz ) {
			case '':
			case 'content-horiz-left':
				$align_horiz = 'flex-start';
				break;
			case 'content-horiz-center':
				$align_horiz = 'center';
				break;
			case 'content-horiz-right':
				$align_horiz = 'flex-end';
				break;
		}
		$res_ctx->load_settings_raw( 'tdb_mts_align_horiz_' . $style_atts_uid, $align_horiz );


		// Space between tags
		$res_ctx->load_settings_raw( 'tdb_mts_space_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'space' ) . 'px' );

		// Padding
		$padding = $res_ctx->get_shortcode_att( 'padding' );
		$padding .= ( $padding != '' && is_numeric( $padding ) ) ? 'px' : '';
		$res_ctx->load_settings_raw( 'tdb_mts_padding_' . $style_atts_uid, $padding );

		// Border size
		$all_border = $res_ctx->get_shortcode_att( 'all_border' );
		$all_border .= $all_border != '' && is_numeric( $all_border ) ? 'px' : '';
		$res_ctx->load_settings_raw( 'tdb_mts_all_border_' . $style_atts_uid, $all_border );

		// Border style
		$all_border_style = $res_ctx->get_shortcode_att( 'all_border_style' );
		$all_border_style = !empty( $all_border_style ) ? $all_border_style : 'solid';
		$res_ctx->load_settings_raw( 'tdb_mts_all_border_style_' . $style_atts_uid, $all_border_style );

		// Border radius
		$radius = $res_ctx->get_shortcode_att( 'radius' );
		$radius .= is_numeric( $radius ) ? 'px' : '';
		$res_ctx->load_settings_raw( 'tdb_mts_radius_' . $style_atts_uid, $radius );


		// Separator icon size
		$ico_size = $res_ctx->get_shortcode_att( 'ico_size' );
		$ico_size .= is_numeric( $ico_size ) ? 'px' : '';
		$res_ctx->load_settings_raw( 'tdb_mts_ico_size_' . $style_atts_uid, $ico_size );
		
		// Separator icon align
		$res_ctx->load_settings_raw( 'tdb_mts_ico_align_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'ico_align' ) . 'px' );
		


		/* -- Colors -- */
		$res_ctx->load_settings_raw( 'tdb_mts_bg_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'bg' ) );
		$res_ctx->load_settings_raw( 'tdb_mts_bg_h_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'bg_h' ) );

		$res_ctx->load_settings_raw( 'tdb_mts_color_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'color' ) );
		$res_ctx->load_settings_raw( 'tdb_mts_color_h_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'color_h' ) );

		$all_border_color = !empty( $all_border_color ) ? $all_border_color : '#000';
		$res_ctx->load_settings_raw( 'tdb_mts_all_border_color_' . $style_atts_uid, $all_border_color );
		$res_ctx->load_settings_raw( 'tdb_mts_border_color_h_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'border_color_h' ) );

		$res_ctx->load_settings_raw( 'tdb_mts_ico_color_' . $style_atts_uid, $res_ctx->get_shortcode_att( 'ico_color' ) );



		/* -- Fonts -- */
		$res_ctx->load_font_settings( 'f_txt', '', 'tdb_mts_', '_' . $style_atts_uid );

	}


    function render( $atts, $content = null ) {

		$additional_classes_array = array();


		/* -- Call the parent render method -- */
        parent::render($atts);



		/* -- Block atts -- */
		// Taxonomy
		$taxonomy = $this->get_att( 'taxonomy' ) != '' ? $this->get_att( 'taxonomy' ) : 'category';

		// Tags limit
		$limit = $this->get_att( 'terms_limit' ) != '' ? $this->get_att( 'terms_limit' ) : 1;

		// Open link in new tab
		$open_in_new_tab = $this->get_att( 'open_in_new_tab' );
		$link_target = $open_in_new_tab != '' ? ' target="blank"' : '';

		// Separator icon
		$icon_sep = $this->get_icon_att( 'tdicon_sep' );
        $icon_sep_data = '';
        if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
            $icon_sep_data = 'data-td-svg-icon="' . $this->get_att('tdicon_sep') . '"';
        }
        $buffy_icon_sep = '';
        if ( !empty( $icon_sep ) ) {
            if( base64_encode( base64_decode( $icon_sep ) ) == $icon_sep ) {
                $buffy_icon_sep .= '<span class="tdb-module-term-sep" ' . $icon_sep_data . '>' . base64_decode( $icon_sep ) . '</span>';
            } else {
                $buffy_icon_sep .= '<i class="tdb-module-term-sep ' . $icon_sep . '"></i>';
            }
        }



		/* -- Retrieve the module post data -- */
		$post_obj = self::$post_obj;

		// Set a flag to determine if the selected taxonomy exists
		$taxonomy_exists = taxonomy_exists($taxonomy);

		// Create an array of dummy terms
		$terms_list_dummy = array();
		for( $i = 1; $i <= $limit; $i++ ) {
			$terms_list_dummy[] = array(
				'id' => $i,
				'name' => 'Sample term ' . $i,
				'url' => '#'
			);
		}

		$terms_list = array();

        if ( gettype($post_obj) === 'object' && get_class($post_obj) === 'WP_Post' ) {
            $td_post_theme_settings = self::$post_theme_settings_meta;
            // check primary term/cat is set
            if ( !empty($td_post_theme_settings['td_primary_cat']) && $limit == 1 ) {
                $selected_term_obj = get_term($td_post_theme_settings['td_primary_cat']);
                $post_terms[] = $selected_term_obj;
            } else {
                $post_terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'object_ids' => $post_obj->ID,
                    'number' => $limit
                ));
            }

			if ( $taxonomy_exists ) {
				// If the selected taxonomy exists, and there are terms of this type 
				// then create an array of them; otherwise if we are in composer, assign
				// it the dummy terms array
				if( !empty( $post_terms ) ) {
					foreach( $post_terms as $term ) {
						$terms_list[] = array(
							'id' => $term->term_id,
							'name' => $term->name,
							'url' => get_term_link( $term->term_id )
						);
					}

                    // check primary term/cat is set
                    if ( !empty($td_post_theme_settings['td_primary_cat']) ) {
                        //we have a custom category selected
                        $selected_term_obj = get_term($td_post_theme_settings['td_primary_cat']);
                        // unset primary if it is found in the term list array
                        foreach( $terms_list as $key => $term ) {
                            if ( $selected_term_obj->term_id === $term['id'] ) {
                                unset($terms_list[$key]);
                                break;
                            }
                        }
                        $selected_term = array(
                            'id' => $selected_term_obj->term_id,
                            'name' => $selected_term_obj->name,
                            'url' => get_term_link( $selected_term_obj->term_id )
                        );
                        // add the primary term first in the term list array
                        array_unshift($terms_list, $selected_term);
                    }
				} else {
					if( td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax() ) {
						// If we are in composer, display dummy data only if we
						// are editing the actual module
						if( tdb_state_template::get_template_type() == 'module' ) {
							$terms_list = $terms_list_dummy;
						}
					}
				}
			}

		} else {
			$terms_list = $terms_list_dummy;
		}


		/* -- Output the module element HTML -- */
        $buffy = '';

		// get the block css
		$buffy .= $this->get_block_css();

		// get the js for this block
		$buffy .= $this->get_block_js();


		$buffy .= '<div class="' . $this->get_block_classes($additional_classes_array) . '" ' . $this->get_block_html_atts() . '>';

			// If the selected taxonomy doesn't exist, then display a warning;
			// otherwise proceed with trying to display the post terms
			if( !$taxonomy_exists ) {
					$buffy .= td_util::get_block_error('Module Taxonomies', 'The selected taxonomy does not exist.');

				$buffy .= '</div>';

				return $buffy;
			}

			foreach( $terms_list as $key => $term ) {
				if( $key != key($terms_list) ) {
					$buffy .= $buffy_icon_sep;
				}

				$buffy .= '<a class="tdb-module-term" href="' . $term['url'] . '"' . $link_target . '>' . $term['name'] . '</a>';
			}
		$buffy .= '</div>';


        return $buffy;

    }

}