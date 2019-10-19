<?php

namespace cw\divi\module;

class FieldGroup{
  public $name;
  public $id;
  public $priority;
  public $tab = 'general';
  public $fields = [];
  private $module;

  public function __construct($module, $id, $name=''){
    if($name === '')
      $name = $id;

    $this->module = $module;
    $this->name   = $name;
    $this->id     = $id;
  }

  public function removeField($id){
    unset($this->fields[$id]);
  }

  public function addField($id){
    $this->fields[$id] = new \cw\divi\module\Field($this->module, $id);
    $this->fields[$id]->tabSlug($this->tab)
                      ->basicOption();

    $this->publishToggles();
    return $this->fields[$id];
  }

  public function addSeperator(){
    return $this->addField('__hidden__'.mt_rand())->typeSeperator();
  }

  public function priority($value){
    $this->priority = $value;
    return $this;
  }

  protected function updateTab($tab){
    $this->tab = $tab;

    foreach($this->fields as $field)
      $field->tabSlug($tab);

    return $this->publishToggles();
  }

  protected function publishToggles(){
    foreach($this->fields as $field)
      $this->module->settings_modal_toggles[$this->tab]['toggles'][$this->id] = [
        'title'    => $this->name,
        'priority' => $this->priority
      ];

    return $this;
  }

  public function tabGeneral(){
    return $this->updateTab('general');
  }

  public function tabAdvanced(){
    return $this->updateTab('advanced');
  }

  public function tabCustomCss(){
    return $this->updateTab('custom_css');
  }

  public function tab($name){
    return $this->updateTab($name);
  }
}
