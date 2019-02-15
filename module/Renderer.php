<?php

namespace cw\divi\module;

class Renderer{

  public static function renderCustomMarginAndPadding($order_class, $fields, $atts, $content, $function_name){
    $order_class = \ET_Builder_Element::add_module_order_class($order_class, $function_name );

    $result = $resultDesktop = $resultTablet = $resultMobile = '';
    foreach($fields as $id => $config){

      if($config['type'] == 'custom_margin'){
        $desktop = $mobile = $tablet = '';
        $selector = str_replace('%%order_class%%', '.'.trim($order_class), $config['custom_margin_selector']);

        $desktop.="margin: ".self::renderMarginPaddingSetting($atts[$id]).";";
        if(isset($atts['custom_margin_tablet']))
          $tablet.="margin: ".self::renderMarginPaddingSetting($atts[$id.'_tablet']).";";
        if(isset($atts['custom_margin_phone']))
          $mobile.="margin: ".self::renderMarginPaddingSetting($atts[$id.'_phone']).";";

        if($desktop) $resultDesktop.="$selector{ $desktop }";
        if($tablet)  $resultTablet.="$selector{ $tablet }";
        if($mobile)  $resultMobile.="$selector{ $mobile }";
      }

      if($config['type'] == 'custom_padding'){
        $desktop = $mobile = $tablet = '';
        $selector = str_replace('%%order_class%%', '.'.trim($order_class), $config['custom_padding_selector']);

        $desktop.="padding: ".self::renderMarginPaddingSetting($atts[$id]).";";
        if(isset($atts['custom_padding_tablet']))
          $tablet.="padding: ".self::renderMarginPaddingSetting($atts[$id.'_tablet']).";";
        if(isset($atts['custom_padding_phone']))
          $mobile.="padding: ".self::renderMarginPaddingSetting($atts[$id.'_phone']).";";

        if($desktop) $resultDesktop.="$selector{ $desktop }";
        if($tablet)  $resultTablet.="$selector{ $tablet }";
        if($mobile)  $resultMobile.="$selector{ $mobile }";
      }

    }
    if($resultDesktop) $result.="$resultDesktop";
    if($resultTablet)  $result.="@media (min-width: 768px) and (max-width: 980px){ $resultTablet }";
    if($resultMobile)  $result.="@media (max-width: 767px){ $resultMobile }";

    return $result;
  }

  public static function renderCustomCss($order_class, $advanced_fields, $atts, $content, $function_name){
    if(!isset($advanced_fields))
      return;

    $order_class = \ET_Builder_Element::add_module_order_class($order_class, $function_name );
    $result = '';
    if(isset($advanced_fields['fonts']))
      $result.=self::renderCustomFonts($advanced_fields, $atts, $content, $order_class);

    if(isset($advanced_fields['max_width']))
      $result.=self::renderMaxWidth($advanced_fields, $atts, $content, $order_class);

    if(isset($advanced_fields['margin_padding']))
      $result.=self::renderMarginPadding($advanced_fields, $atts, $content, $order_class);

    if(isset($advanced_fields['borders']))
      $result.=self::renderBorders($advanced_fields, $atts, $content, $order_class);

    if(isset($advanced_fields['button']))
      $result.=self::renderButtons($advanced_fields, $atts, $content, $order_class);

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


  public static function renderButtons($advanced_fields, $atts, $content, $order_class){
    $result = $resultDesktop = $resultTablet = $resultMobile = '';
    foreach($advanced_fields['button'] as $name => $config){
      $desktop = $mobile = $tablet = '';
      if(!isset($config['css']))
        continue;

      $selector = str_replace('%%order_class%%', '.'.trim($order_class), $config['css']['main']);

      $borderWidth = $name.'_border_width';
      $borderColor = $name.'_border_color';
      $borderStyle = $name.'_border_style';
      $borderRadii = $name.'_border_radius';

      $resultDesktop.= self::renderBorderStyle($selector, 'border', $atts[$borderWidth], $atts[$borderStyle], $atts[$borderColor]);
      $resultDesktop.= self::renderBorderRadii($selector, $atts[$borderRadii], '3');
      self::renderFontSettingResponsive($atts, $name.'_alignment',     'text-align',     '', $desktop, $tablet, $mobile);
      self::renderFontSettingResponsive($atts, $name.'_text_size',      'font-size',      'px', $desktop, $tablet, $mobile);
      self::renderFontSettingResponsive($atts, $name.'_letter_spacing', 'letter-spacing', '', $desktop, $tablet, $mobile);

      self::renderFontSetting($atts, $name.'_text_color', 'color', '', $desktop);
      if(isset($atts[$name.'_bg_color']))
        $desktop.='background:'.$atts[$name.'_bg_color'].';';

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

    if($resultDesktop)
      $result.="$resultDesktop";

    if($resultTablet)
      $result.="@media (min-width: 768px) and (max-width: 980px){ $resultTablet }";

    if($resultMobile)
      $result.="@media (max-width: 767px){ $resultMobile }";

    return $result;
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
            $font_values[1] = 'on' === $font_values[1] ? '500' : $font_values[1];
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

  public static function renderCustomCssFields($order_class, $custom_css, $atts, $content, $function_name){
    if(!isset($custom_css))
      return;

    if($order_class !== false)
      $order_class = '.'.trim(\ET_Builder_Element::add_module_order_class($order_class, $function_name ));
    else
      $order_class = '';

    $result = '';
    foreach($custom_css as $slug => $options){
      if(!isset($atts['custom_css_'.$slug]))
        continue;

      $value = $atts['custom_css_'.$slug];
      $result.=' '.$order_class.$options['selector'].'{'.$value.'} ';
    }

    return $result;
  }

  public static function renderBorders($advanced_fields, $atts, $content, $order_class){
    if(!isset($advanced_fields['borders']))
      return '';

    $result = '';
    foreach($advanced_fields['borders'] as $name => $config){
      if($name == 'default')
        $suffix = '';
      else
        $suffix = '_'.$name;

      foreach($config['css'] as $option => $selector){
        $desktop = '';
        $selectorStyles = str_replace('%%order_class%%', '.'.trim($order_class), $selector['border_styles']);
        $selectorRadii  = str_replace('%%order_class%%', '.'.trim($order_class), $selector['border_radii']);

        $borderWidth = 'border_width_all'.$suffix;
        $borderColor = 'border_color_all'.$suffix;
        $borderStyle = 'border_style_all'.$suffix;
        $borderRadii = 'border_radii'.$suffix;

        $result.= self::renderBorderStyle($selectorStyles, 'border', $atts[$borderWidth], $atts[$borderStyle], $atts[$borderColor]);
        $result.= self::renderBorderRadii($selectorRadii, $atts[$borderRadii]);

        foreach(['top', 'left', 'right', 'bottom'] as $partial){
          $borderWidth = 'border_width_'.$partial.$suffix;
          $borderColor = 'border_color_'.$partial.$suffix;
          $borderStyle = 'border_style_'.$partial.$suffix;

          $result.= self::renderBorderStyle($selectorStyles, 'border-'.$partial, $atts[$borderWidth], $atts[$borderStyle], $atts[$borderColor]);
        }
      }
    }
    return $result;
  }

  public static function renderBorderStyle($selector, $key, $width, $style, $color){
    if(empty($width)
    && empty($style)
    && empty($color))
      return '';

    if(empty($style))
      $style = "solid";

    if(empty($color))
      $color = 'inherit';

    if(empty($width))
      $width = 'inherit';
    else if(strpos($width, 'px') === false)
      $width.='px';

    return "$selector{ $key: $width $style $color; }";
  }

  public static function renderBorderRadii($selector, $radii, $default=''){
    if(strpos($radii, '|') === false){
      if(empty($radii)){
        if(empty($default))
          return '';
        else
          $radii = $default;
      }

      return "$selector{ border-radius: {$radii}px; }";
    }
    $tmp = explode('|', $radii);
    array_shift($tmp);
    $tmp = implode(' ', $tmp);
    return "$selector{ border-radius: $tmp; }";
  }

  public static function renderMaxWidth($advanced_fields, $atts, $content, $order_class){
    if(!isset($advanced_fields['max_width'])
    || !isset($advanced_fields['max_width']['css']))
      return '';

    $result = $resultDesktop = $resultTablet = $resultMobile = '';
    foreach($advanced_fields['max_width']['css'] as $option => $selector){
      $desktop = $mobile = $tablet = '';
      $selector = str_replace('%%order_class%%', '.'.trim($order_class), $selector);

      if(isset($atts['max_width']))
        $desktop.="max-width: {$atts['max_width']};";
      if(isset($atts['max_width_tablet']))
        $tablet.="max-width: {$atts['max_width_tablet']};";
      if(isset($atts['max_width_phone']))
        $mobile.="max-width: {$atts['max_width_phone']};";

      if($desktop) $resultDesktop.="$selector{ $desktop }";
      if($tablet)  $resultTablet.="$selector{ $tablet }";
      if($mobile)  $resultMobile.="$selector{ $mobile }";
    }

    if($resultDesktop) $result.="$resultDesktop";
    if($resultTablet)  $result.="@media (min-width: 768px) and (max-width: 980px){ $resultTablet }";
    if($resultMobile)  $result.="@media (max-width: 767px){ $resultMobile }";

    return $result;
  }

  public static function renderMarginPaddingSetting($input){
    $tmp = explode('|', $input);
    $result = '';
    foreach($tmp as $value){
      if(empty($value))
        $result.='0 ';
      else
        $result.=$value.' ';
    }

    return $result;
  }

  public static function renderMarginPadding($advanced_fields, $atts, $content, $order_class){
    if(!isset($advanced_fields['margin_padding'])
    || !isset($advanced_fields['margin_padding']['css']))
      return '';

    $result = $resultDesktop = $resultTablet = $resultMobile = '';
    foreach($advanced_fields['margin_padding']['css'] as $option => $selector){
      $desktop = $mobile = $tablet = '';
      $selector = str_replace('%%order_class%%', '.'.trim($order_class), $selector);

      if($option == 'margin'){
        if(isset($atts['custom_margin']))
          $desktop.="margin: ".self::renderMarginPaddingSetting($atts['custom_margin']).";";
        if(isset($atts['custom_margin_tablet']))
          $tablet.="margin: ".self::renderMarginPaddingSetting($atts['custom_margin_tablet']).";";
        if(isset($atts['custom_margin_phone']))
          $mobile.="margin: ".self::renderMarginPaddingSetting($atts['custom_margin_phone']).";";
      }

      if($option == 'padding'){
        if(isset($atts['custom_padding']))
          $desktop.="padding: ".self::renderMarginPaddingSetting($atts['custom_padding']).";";
        if(isset($atts['custom_padding_tablet']))
          $tablet.="padding: ".self::renderMarginPaddingSetting($atts['custom_padding_tablet']).";";
        if(isset($atts['custom_padding_phone']))
          $mobile.="padding: ".self::renderMarginPaddingSetting($atts['custom_padding_phone']).";";
      }

      if($desktop) $resultDesktop.="$selector{ $desktop }";
      if($tablet)  $resultTablet.="$selector{ $tablet }";
      if($mobile)  $resultMobile.="$selector{ $mobile }";
    }
    if($resultDesktop) $result.="$resultDesktop";
    if($resultTablet)  $result.="@media (min-width: 768px) and (max-width: 980px){ $resultTablet }";
    if($resultMobile)  $result.="@media (max-width: 767px){ $resultMobile }";

    return $result;
  }
}
