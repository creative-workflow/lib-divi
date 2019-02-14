<?php

namespace cw\divi\module;

class Field{
  public $config = [];
  public $id;
  private $module;

  public function __construct($module, $id){
    $this->module = $module;
    $this->id     = $id;
  }

  public function label($value=null){
    return $this->getOrSet('label', $value);
  }

  public function type($value=null, $default=null){
    return $this->getOrSet('type', $value, $default);
  }

  public function typeUpload(){
    $this->whitelist();
    return $this->type('upload');
  }

  public function typeText($default=null){
    $this->whitelist();
    return $this->type('text', $default);
  }

  public function typeTextarea($default=null, $readonly = false){
    $this->whitelist()->getOrSet('readonly', $readonly ? 'readonly' : '');
    return $this->type('textarea', $default);
  }

  public function typeCode($default=null){
    $this->whitelist()->getOrSet('mode', 'html');
    return $this->type('codemirror', $default);
  }

  public function typeInfo($content, $cssClass='info-box'){
    $this->whitelist();

    return $this->typeText($content, true)
                ->addCssClass($cssClass);
  }

  public function typeDatePicker(){
    $this->whitelist();
    return $this->type('date_picker');
  }

  public function typeSwitch($default='off'){
    $this->whitelist();

    if($default === true)
      $default = 'on';
    if($default === false)
      $default = 'off';

    $this->type('yes_no_button', $default);

    return $this->getOrSet('options', [
      'off' => esc_html__( 'No', 'et_builder' ),
      'on'  => esc_html__( 'Yes', 'et_builder' ),
    ]);
  }

  public function typeRange($min=1, $max=100, $step=1, $unit='%', $default=null, $mobileOptions = false, $validateUnit = true){
    $this->whitelist();
    $this->type('range', $default);
    $this->getOrSet('mobile_options', $mobileOptions);
    $this->getOrSet('validate_unit', $validateUnit);
    return $this->getOrSet('range_settings', [
      'min'  => $min,
      'max'  => $max,
      'step' => $step,
      'fixed_unit' => $unit
    ]);
  }

  public function typeSelect($options, $default=null){
    $this->whitelist();
    $this->type('select', $default);
    return $this->getOrSet('options', $options);
  }

  public function typeSkip(){
    return $this->type('skip');
  }

  public function typeMultipleCheckboxes($options, $default=null){
    $this->module->beforeInstanceAttributes(function($module, &$variables){
      if(!isset($variables[$this->id])
      || is_array($variables[$this->id]))
        return ;

      $options = $this->getOrSet('options');
      $enabled = explode('|', $variables[$this->id]);
      $result  = [];
      $i = 0;
      foreach($options as $value => $name){
        if(!empty($enabled[$i]))
          $result[] = $value;
        $i++;
      }
      $variables[$this->id] = $result;
    });
    $this->whitelist();
    $this->type('multiple_checkboxes', $default);
    return $this->getOrSet('options', $options);
  }

  // attention works because of divi internal only one time per module -> douplicate ids
  public function typeHtml(){
    $this->module->beforeInstanceAttributes(function($module, &$variables){
      $variables[$this->id] = $module->content;
    });

    return $this->type('tiny_mce');
  }

  public function showIf($input = []){
    $this->getOrSet('show_if', $input);
    return $this;
  }

  public function showIfNot($input = []){
    $this->getOrSet('show_if_not', $input);
    return $this;
  }

  public function typeColor($default=''){
    return $this->type('color-alpha', $default);
  }

  public function typeMargin($selector, $mobileOptions = true, $parentSelector = '%%order_class%%'){
    $this->getOrSet('mobile_options', $mobileOptions);
    $this->getOrSet('custom_margin_selector', "$parentSelector $selector");
    return $this->type('custom_margin');
  }

  public function typePadding($selector='', $mobileOptions = true, $parentSelector = '%%order_class%%'){
    $this->getOrSet('mobile_options', $mobileOptions);
    $this->getOrSet('custom_padding_selector', "$parentSelector $selector");
    return $this->type('custom_padding');
  }

  public function basicOption(){
    return $this->getOrSet('option_category', 'basic_option');
  }

  public function configurationOption(){
    return $this->getOrSet('option_category', 'configuration');
  }

  public function layoutOption(){
    return $this->getOrSet('option_category', 'layout');
  }

  public function optionClass($value=null){
    return $this->getOrSet('option_class', $value);
  }

  public function optionId($value=null){
    return $this->getOrSet('id', $value);
  }

  public function addCssClass($value){
    $class = $this->getOrSet('option_class');
    return $this->getOrSet('option_class', "$class $value");
  }

  public function description($value=null){
    return $this->getOrSet('description', $value);
  }

  public function additionalAttribute($value=null){
    return $this->getOrSet('additional_att', $value);
  }

  public function tabSlug($value=null){
    return $this->getOrSet('tab_slug', $value);
  }

  public function whitelist(){
    $this->module->whitelisted_fields[] = $this->id;
    return $this;
  }

  public function defaultValue($value){
    $this->module->fields_defaults[$this->id] = [$value];
    $this->getOrSet('default', $default);
    return $this;
  }

  public function addFontSettings($cssElement, $name = null, $id = null){
    if($name === null)
      $name = $this->getOrSet('label');

    if($id === null)
      $id = $this->id;

    $this->module->addFontSettings($cssElement, $name, $id);

    return $this;
  }

  public function getOrSet($key, $value=null, $default=null){
    if($value === null)
      return (isset($this->config[$key])) ? $this->config[$key] : null;

    if($default !== null)
      $this->defaultValue($default);

    $this->config[$key] = $value;
    return $this;
  }
}
