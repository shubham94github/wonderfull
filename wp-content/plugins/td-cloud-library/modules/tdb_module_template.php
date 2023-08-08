<?php

class tdb_module_template {

	var $module_template_obj;
	var $template_class;
	var $post_obj;


	function __construct( $post, $module_template_id, $flex_block_atts = array() ) {

        if ( is_array($post) && !empty( $post['post_id'] ) ) {
            $wp_post_obj = get_post( $post['post_id'] );
        } else {
            $wp_post_obj = get_post( $post );
        }

		$this->module_template_obj = get_post( $module_template_id );
		$this->template_class = 'tdb_module_template_' . $module_template_id;
		$this->post_obj = $wp_post_obj;

		global $tdb_module_template_params;
		$tdb_module_template_params = array(
			'template_obj' => $this->module_template_obj,
			'template_class' => $this->template_class,
			'post_obj' => $this->post_obj,
			'shortcodes' => array()
		);

	}


	function render() {
		ob_start();
	
		td_global::set_in_tdb_module_template(true);

		$module_classes = array(
			'td_module_wrap',
			$this->template_class,
			'td-animation-stack',
			'td-cpt-'. $this->post_obj->post_type
		);

		?>

		<div class="<?php echo implode(' ', $module_classes ); ?>">
			<div class="td-module-container">
                <?php

                // build module tpl edit btn
                $module_tpl_edit_url = add_query_arg(
	                array(
		                'post_id' => $this->module_template_obj->ID,
		                'td_action' => 'tdc',
		                'tdbTemplateType' => 'module',
		                'tdbLoadDataFromId' => $this->post_obj->ID,
		                'prev_url' => rawurlencode( tdc_util::get_current_url() ),
	                ),
	                admin_url( 'post.php' )
                );

                // add module tpl edit btn
                if ( current_user_can('edit_published_posts') ) {
	                echo '<a class="tdb-module-template-edit" href="' . $module_tpl_edit_url . '" target="_blank">edit module template</a>';
                }

                if ( td_global::get_in_menu() ) {
	                echo do_shortcode( $this->module_template_obj->post_content );
                } else {
	                td_global::set_in_element( true );
	                echo do_shortcode( $this->module_template_obj->post_content );
	                td_global::set_in_element( false );
                }

                ?>
			</div>
		</div>

		<?php

		td_global::set_in_tdb_module_template(false);

        return ob_get_clean();
	}

}