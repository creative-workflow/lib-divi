<?php

namespace cw\divi\module;

class Field{
  public $config = [];
  public $id;
  private $module;

  public function __construct(\ET_Builder_Module $module, $id){
    $this->module = $module;
    $this->id     = $id;
  }

  public function label($value=null){
    return $this->getOrSet('label', $value);
  }

  public function type($value=null, $default=''){
    return $this->getOrSet('type', $value, $default);
  }

  public function typeUpload(){
    $this->whitelist();
    return $this->type('upload');
  }

  public function typeText($default=''){
    $this->whitelist();
    return $this->type('text', $default);
  }

  public function typeTextarea($default=''){
    $this->whitelist();
    return $this->type('textarea', $default);
  }

  public function typeDatePicker(){
    $this->whitelist();
    return $this->type('date_picker');
  }

  public function typeSwitch($default='off'){
    $this->whitelist();

    $this->type('yes_no_button', $default);

    return $this->getOrSet('options', [
      'off' => esc_html__( 'No', 'et_builder' ),
      'on'  => esc_html__( 'Yes', 'et_builder' ),
    ]);
  }

  public function typeRange($min=1, $max=100, $step=1, $unit='%', $default=0){
    $this->whitelist();
    $this->type('range', $default);
    $this->getOrSet('default', $default);

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

  // attention works because of divi internal only one time per module -> douplicate ids
  public function typeHtml(){
    $this->module->beforeInstanceAttributes(function($module, &$variables){
      $variables[$this->id] = $module->content;
    });

    return $this->type('tiny_mce');
  }

  public function typeMultipleCheckboxes($options, $default=''){
    $this->type('multiple_checkboxes', $default);
    return $this->getOrSet('options', $options);
  }

  public function typeColor($default=''){
    return $this->type('color-alpha', $default);
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

  protected function getOrSet($key, $value=null, $default=''){
    if($value === null)
      return (isset($this->config[$key])) ? $this->config[$key] : null;

    if($default !== null)
      $this->defaultValue($default);

    $this->config[$key] = $value;
    return $this;
  }
}
