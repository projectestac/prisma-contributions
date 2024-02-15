<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://kiwop.com
 * @since      1.0.0
 *
 * @package    Kiwop_Aportacions_Recursos
 * @subpackage Kiwop_Aportacions_Recursos/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Kiwop_Aportacions_Recursos
 * @subpackage Kiwop_Aportacions_Recursos/public
 * @author     Antonio Sanchez <antonio@kiwop.com>
 */
class Kiwop_Aportacions_Recursos_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	private $excluded_post_types;
    private $wpdb;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
        
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->excluded_post_types = array(
            'post',
            'page', 
            'attachment', 
            'revision', 
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'wp_block',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
            'acf-taxonomy',
            'acf-post-type',
            'acf-ui-options-page',
            'acf-field-group',
            'acf-field',
            'astra-advanced-hook',
            'wpcf7_contact_form',
        );

		$this->plugin_name = $plugin_name;
		$this->version = $version;


        add_action("wp_ajax_kiwopPrismaSearchTags", array($this,"kiwopPrismaSearchTags") );  
        add_action("wp_ajax_nopriv_kiwopPrismaSearchTags", array($this,"kiwopPrismaSearchTags") );  
        
        add_action("wpcf7_before_send_mail", array($this,"wpcf7_add_recurs_to_ddbb") );          


	}

   
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kiwop-aportacions-recursos-public.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

       
        $dir = plugin_dir_url("") . $this->plugin_name;

        $file_js = 'js/kiwop-aportacions-recursos-public.js';
        $pathfile =  plugin_dir_path( __FILE__ ) . $file_js;        
        $file =  plugin_dir_url( __FILE__ ) . $file_js;        
        $v = filemtime( $pathfile );
        wp_enqueue_script( $this->plugin_name, $file, array( 'jquery' ), $v, false );


        // link ajax calls with functions and vars por JS
        wp_localize_script(
            $this->plugin_name, 
            'kiwop_aportacions_recursos_globals', 
            array(
                'ajax_url'  => admin_url('admin-ajax.php'),
                'admin_url' => admin_url( 'admin.php' ),
                
                // aqui todos los nonce del backend                
                'kiwopPrismaSearchTags' => wp_create_nonce('kiwopPrismaSearchTags_nonce' ),       
                'close' => $dir . '/public/img/close.png',
                'loader' => $dir . '/public/img/ajax-loader-mini.gif',
                'loader_xl' => $dir . '/public/img/ajax-loader.gif',

            )
        );

	}

    public function kiwopPrismaSearchTags() 
    {
        check_ajax_referer( 'kiwopPrismaSearchTags_nonce' );
        
        $search_text = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        if (empty($search_text) || strlen($search_text) < 3) {
            return wp_send_json_success([
                'tags' => [],
            ]);
        }

        
        $sql = " SELECT t.term_id, t.`name` AS tag 
            FROM wp_term_relationships tr
            LEFT JOIN wp_term_taxonomy tt ON (tt.`term_taxonomy_id`=tr.`term_taxonomy_id` AND tt.`taxonomy`='post_tag')
            LEFT JOIN wp_terms t ON (tt.`term_id`=t.`term_id`)
            WHERE 
                t.name LIKE '%".esc_sql($search_text)."%'
            GROUP BY t.term_id, t.name 
            ORDER BY t.term_id, t.name 
            LIMIT 10        
        ";
        
        $res = $this->wpdb->get_results($sql);
        
        return wp_send_json_success([
            'tags' => $res,
        ]);

    
    }
        

    public function wpcf7_add_recurs_to_ddbb($wpcf) {

        $response = [];
        $response['message'] = '';

        $submission = WPCF7_Submission::get_instance();

        if ($submission) {

            $posted_data = $submission->get_posted_data();


            // Crear un nuevo post
            $post_data = array(
                'post_title'    => $posted_data['titol'],  
                'post_content'  => $posted_data['descripcio'], 
                'post_status'   => 'draft',  
                'post_type'     => $posted_data['posttype'][0],
            );

            $post_id = wp_insert_post($post_data);

            
            if (!is_wp_error($post_id)) {

                ################### etiquetas ################################################
                $data_tags = json_decode(base64_decode($posted_data['kiwop_prisma_etiquetes_hidden']),true);
                if (count($data_tags)) {
                    //echo var_export($data_tags,1);die();
                    foreach ($data_tags as $etiqueta) {
                        if (empty($etiqueta['term_id'])) {
                            // Comprueba si la etiqueta ya existe, si no, créala
                            $term_id = term_exists($etiqueta['tag'], 'post_tag');
                        
                            if (!$term_id) {
                                $term_id = wp_insert_term($etiqueta['tag'], 'post_tag');
                            }
                        } else {
                            $term_id = $etiqueta['term_id'];
                        }
                    
                        // Asocia la etiqueta al post
                        if (!is_wp_error($term_id)) {
                            wp_set_post_tags($post_id, $etiqueta['tag'], true);
                        } 
                    }                    
                }

                $uploaded_files = $submission->uploaded_files();

                // Procesa las imágenes
                foreach ($uploaded_files as $field_name => $file_paths) {                    
                    foreach ($file_paths as $file_path) {

                        $filename = explode("/",$file_path)[count(explode("/",$file_path))-1];                       

                        if ($field_name == 'Imatgedestacada') {

                            $file = file_get_contents($file_path);
                            $infoArchivo = wp_upload_bits($filename, '', $file);
    
                            // Verificar si la subida fue exitosa
                            if ($infoArchivo['error']) {
                                $messages['Error al subir el archivo: ' . $infoArchivo['error']] = true;
                                return false;
                            } else {
                                // Configurar los metadatos del archivo
                                $attachment = array(
                                    'post_mime_type' => mime_content_type($file_path),
                                    'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                                );
                            
                                // Insertar el archivo en la biblioteca de medios
                                $attachment_id = wp_insert_attachment($attachment, $infoArchivo['file']);
                                
                                // Verificar si la subida fue exitosa
                                if (is_wp_error($attachment_id)) {
                                    $this->endWithMessage("Error al crear la imagen destacada del post.",true);
                                } else {
                                    set_post_thumbnail($post_id, $attachment_id);
                                    # borramos archivo temporal
                                    //unlink($file_path);            
                                }                        
                            }                        
    
                        } else {
                            $file = file_get_contents($file_path);
                            $infoArchivo = wp_upload_bits($filename, '', $file);
    
                            // Verificar si la subida fue exitosa
                            if ($infoArchivo['error']) {
                                $messages['Error al subir el archivo: ' . $infoArchivo['error']] = true;
                                return false;
                            } else {
                                // Configurar los metadatos del archivo
                                $attachment = array(
                                    'post_mime_type' => mime_content_type($file_path),
                                    'post_title'     => preg_replace('/\.[^.]+$/', '', $filename),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                                );
                            
                                // Insertar el archivo en la biblioteca de medios
                                $recurso_id = wp_insert_attachment($attachment, $infoArchivo['file']);
                                
                                // Verificar si la subida fue exitosa
                                if (is_wp_error($recurso_id)) {
                                    $this->endWithMessage("Error al crear el archivo adjunto al post.",true);
                                } else {
                                    update_field('attachments', $recurso_id, $post_id);
                                    //update_post_meta($post_id, 'fitxer_del_recurs_associat_id', $recurso_id);
                                }     
                            }                            
                        }                        
                    }
                }                

            } else {
                $this->endWithMessage( 'Error al crear el post: ' . $post_id->get_error_message(),true);
            }
                    
        }
     
        return $submission;
    }

    
    private function endWithMessage($msg,$error = false) {
        $response = [];
        $response['message'] = $msg;
        $response['status'] = 'validation_failed';
        $response['error'] = $error;
        echo json_encode($response);
        die();         
    }

}
