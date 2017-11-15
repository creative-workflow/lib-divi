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

  public function typeSwitch($default='off'){
    $this->whitelist();
    return $this->type('yes_no_button', $default);
  }

  public function typeSelect($options, $default=null){
    $this->whitelist();
    $this->type('select', $default);
    return $this->getOrSet('options', $options);
  }

  public function typeSkip(){
    return $this->type('skip');
  }

  public function typeHtml(){
    $this->module->beforeRender(function($module, &$variables){
      $variables[$this->id] = $module->shortcode_content;
    });
    
    return $this->type('tiny_mce');
  }

  public function typeMultipleCheckboxes($options, $default=''){
    $this->type('multiple_checkboxes', $default);
    return $this->getOrSet('options', $options);
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

  public function addFontSettings($cssElement, $name = null){
    if($name === null)
      $name = $this->getOrSet('label');

    if(!is_array($this->module->advanced_options))
      $this->module->advanced_options = [];

    if(!is_array($this->module->advanced_options['fonts']))
      $this->module->advanced_options['fonts'] = [];

    $this->module->advanced_options['fonts'][$this->id] = [
      'label'    => $name . ' Text',
      'css'      => [
        'main' => "{$this->main_css_element} {$cssElement}",
        // 'line_height' => "{$this->main_css_element} p",
        // 'color' => "{$this->main_css_element}.et_pb_text",
      ],
    ];

    return $this;
  }

  protected function getOrSet($key, $value=null, $default=''){
    if($value === null)
      return $this->config[$key];

    if($default !== null)
      $this->defaultValue($default);

    $this->config[$key] = $value;
    return $this;
  }
}
