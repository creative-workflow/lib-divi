<?php

namespace cw\divi\module;

class Renderer{

  public static function renderCustomCss($order_class, $advanced_fields, $atts, $content, $function_name){
    if(!isset($advanced_fields))
      return;

    $order_class = \ET_Builder_Element::add_module_order_class($order_class, $function_name );
    $result = '';
    if(isset($advanced_fields['fonts']))
      $result.=self::renderCustomFonts($advanced_fields, $atts, $content, $order_class);

    return $result;
  }

  public static function renderFontSettingResponsive($atts, $key, $style, $unit, &$desktop, &$tablet, &$mobile){
    if(!isset($atts[$key]))
      return ;

    $desktop.="$style: {$atts[$key]}$unit;";
    if(isset($atts[$key.'_tablet']))
      $tablet.="$style: {$atts[$key.'_tablet']}$unit;";
    if(isset($atts[$key.'_phone']))
      $mobile.="$style: {$atts[$key.'_phone']}$unit;";
  }

  public static function renderFontSetting($atts, $key, $style, $unit, &$desktop){
    if(!isset($atts[$key]))
      return ;

    $desktop.="$style: {$atts[$key]}$unit;";
  }

  public static function renderCustomFonts($advanced_fields, $atts, $content, $order_class){
    $result = $resultDesktop = $resultTablet = $resultMobile = '';
    foreach($advanced_fields['fonts'] as $name => $config){
      if(!isset($config['css']))
        continue;

      # echo '<pre>'; var_dump($atts); die();
      foreach($config['css'] as $option => $selector){
        $desktop = $mobile = $tablet = '';
        $selector = str_replace('%%order_class%%', '.'.trim($order_class), $selector);

        self::renderFontSettingResponsive($atts, $name.'_text_align',     'text-align',     '', $desktop, $tablet, $mobile);
        self::renderFontSettingResponsive($atts, $name.'_font_size',      'font-size',      'px', $desktop, $tablet, $mobile);
        self::renderFontSettingResponsive($atts, $name.'_letter_spacing', 'letter-spacing', '', $desktop, $tablet, $mobile);

        self::renderFontSetting($atts, $name.'_text_color', 'color', '', $desktop);

        if(isset($atts[$name.'_font'])){
          $font_values = explode('|', $atts[$name.'_font']);
          if ( isset( $font_values[1] ) ) {
            $font_values[1] = 'on' === $font_values[1] ? '700' : $font_values[1];
          }

          $font_values          = array_map( 'trim', $font_values );
          $font_name            = $font_values[0];
          $font_weight          = isset( $font_values[1] ) && '' !== $font_values[1] ? $font_values[1] : '';
          $is_font_italic       = isset( $font_values[2] ) && 'on' === $font_values[2] ? true : false;
          $is_font_uppercase    = isset( $font_values[3] ) && 'on' === $font_values[3] ? true : false;
          $is_font_underline    = isset( $font_values[4] ) && 'on' === $font_values[4] ? true : false;
          $is_font_small_caps   = isset( $font_values[5] ) && 'on' === $font_values[5] ? true : false;
          $is_font_line_through = isset( $font_values[6] ) && 'on' === $font_values[6] ? true : false;
          $font_line_color      = isset( $font_values[7] ) ? $font_values[7] : '';
          $font_line_style      = isset( $font_values[8] ) ? $font_values[8] : '';

          if($font_weight)          $desktop.="font-weight: $font_weight;";
          if($is_font_italic)       $desktop.="font-style: italic;";
          if($is_font_uppercase)    $desktop.="text-transform: uppercase;";
          if($is_font_small_caps)   $desktop.="font-variant: small-caps;";
          if($is_font_underline)    $desktop.="text-decoration: underline;";
          if($is_font_line_through) $desktop.="text-decoration: line-through;";
        }

        if($desktop)
          $resultDesktop.="$selector{ $desktop }";

        if($tablet)
          $resultTablet.="$selector{ $tablet }";

        if($mobile)
          $resultMobile.="$selector{ $mobile }";
      }
    }

    if($resultDesktop)
      $result.="$resultDesktop";

    if($resultTablet)
      $result.="@media (min-width: 768px) and (max-width: 980px){ $resultTablet }";

    if($resultMobile)
      $result.="@media (max-width: 767px){ $resultMobile }";

    return $result;
  }
}
