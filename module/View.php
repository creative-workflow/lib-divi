<?php

namespace cw\divi\module;

class View{
  use \cw\php\view\html\traits\Html;

  protected $variables = [];
  protected $parent;

  public function __construct(\cw\divi\module\Extension $parent){
    $this->parent = $parent;
  }

  public function renderModule($view, $variables=[]){
    $this->variables = $variables;

    return $this->_render(
      CW_DIVI_MODULES_FOLDER . '/views/module-wrapper.php',[
        'module_view_file' => $view,
        'view_attributes'  => $variables,
        'data'             => $this->renderData(),
        'module_id'        => $this->getModuleIdWithAttributeIfPresent(),
        'main_css_classes' => implode(' ', $this->getCssClasses())
    ]);
  }

  public function render($view, $variables=[]){
    return $this->_render($this->parent->path . '/' . $view, $variables);
  }

  public function _render($view, $variables=[]){
    ob_start();

      extract($variables);
      $html = \cw\php\view\Html::getInstance();
      include $view;

    return ob_get_clean();
  }

  public function renderData(){
    $data = $this->variables['data'];
    if(!isset($data) || !is_array($data) || empty($data))
      return '';

    return \cw\php\view\html\encoder\Attributes::encodeDataArray($data);
  }

  public function getCssClasses(){
    $classes = [$this->parent->main_css_class];

    if(isset($this->variables['classes']))
      $classes = array_merge($classes, $this->variables['classes']);

    if($this->getModuleClassIfPresent())
      $classes[] = $this->getModuleClassIfPresent();

    return $classes;
  }

  public function getModuleClassIfPresent(){
    if(!isset($this->variables['module_class']))
      return '';

    $module_class = $this->variables['module_class'];
    $module_class = \ET_Builder_Element::add_module_order_class( $module_class, $function_name );
    $module_class = $module_class ? sprintf( ' %1$s', esc_attr( ltrim( $module_class ) ) ) : '';

    return $module_class;
  }

  public function getModuleIdWithAttributeIfPresent(){
    if(!isset($this->variables['module_id']))
      return '';

    $module_id = $this->variables['module_id'];
    $module_id = $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '';
    return $module_id;
  }

  # delegate unknown calls to parent module
  public function __call($method, $args) {
    return call_user_func_array([$this->parent, $method], $args);
  }
}
