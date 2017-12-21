<?php

namespace cw\divi\module;

class FieldGroup{
  public $name;
  public $id;
  public $priority;
  public $tab = 'general';
  public $fields = [];
  private $module;

  public function __construct(\ET_Builder_Module $module, $id, $name=''){
    if($name === '')
      $name = $id;

    $this->module = $module;
    $this->name   = $name;
    $this->id     = $id;
  }

  public function addField($id){
    $this->fields[$id] = new \cw\divi\module\Field($this->module, $id);
    $this->fields[$id]->tabSlug($this->tab)
                      ->basicOption();

    $this->publishToggles();
    return $this->fields[$id];
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
      $this->module->options_toggles[$this->tab]['toggles'][$this->id] = [
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
}
