<?php

namespace cw\divi;

class Helper{
  #https://divibooster.com/enable-divi-builder-on-custom-post-types/
  public static function enableLibraryForCustomLayouts(){
    add_filter( 'et_pb_show_all_layouts_built_for_post_type', function() {
        return 'page';
    });
  }

  public static function enableEditorForCustomPostTypes(){
    /* Enable Divi Builder on all post types with an editor box */
    add_filter('et_builder_post_types', function($post_types) {
      foreach(get_post_types() as $pt) {
        if (!in_array($pt, $post_types)
        && post_type_supports($pt, 'editor'))
          $post_types[] = $pt;
      }
      return $post_types;
    });

    /* Add Divi Custom Post Settings box */
    add_action('add_meta_boxes', function() {
      foreach(get_post_types() as $pt) {
        if (post_type_supports($pt, 'editor')
        && function_exists('et_single_settings_meta_box'))
          add_meta_box('et_settings_meta_box',
                       __('Divi Custom Post Settings', 'Divi'),
                       'et_single_settings_meta_box',
                       $pt,
                       'side',
                       'high');
      }
    });

    /* Ensure Divi Builder appears in correct location */
    add_action('admin_head', function() {
      $s = get_current_screen();
      if(!empty($s->post_type)
      && $s->post_type!='page'
      && $s->post_type!='post') {
      ?>
        <script>
          jQuery(function($){
            $('#et_pb_layout').insertAfter($('#et_pb_main_editor_wrap'));
          });
        </script>
        <style>
          #et_pb_layout { margin-top:20px; margin-bottom:0px }
        </style>
      <?php
      }
    });

    // Ensure that Divi Builder framework is loaded - required for some post types when using Divi Builder plugin
    add_filter('et_divi_role_editor_page', function($page) {
      return isset($_GET['page'])?$_GET['page']:$page;
    });
  }
}
