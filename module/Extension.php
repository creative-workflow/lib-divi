<?php

namespace cw\divi\module;

class Extension extends \ET_Builder_Module {
  protected $groups     = [];
  protected $view       = null;
  protected $uri        = null;
  protected $attributes = null;
  public    $path       = null;
  protected $beforeRenderCallback;

  public function __construct($path = null){
    parent::__construct();
    $this->path = $path;
    $this->uri  = '/modules/' . $this->moduleFolderName();
    $this->enqueueShortcodeAssets();
  }

  public $whitelisted_fields = [];
  public $fields_defaults    = [];
  public $advanced_options   = [];
  public $options_toggles    = [
    'general'  => [
      'toggles' => []
    ],
    'advanced' => [
      'toggles' => []
    ],
    'custom_css' => [
      'toggles' => []
    ],
  ];

  public function init($mainCssClass, $diviModuleSlug) {
    $this->main_css_class   = $mainCssClass;
    $this->main_css_element = '%%order_class%%.'.$mainCssClass;
    $this->slug             = 'et_pb_'.$diviModuleSlug;
    $this->name             = $this->moduleDisplayName();

    $this->fb_support       = true;


    return $this;
  }

  public function enqueueShortcodeAssets(){
    global $post;

    if(!$this->isModuleInPost())
      return ;

    if(file_exists($this->path . '/css/module.css'))
      \cw\wp\Assets::getInstance()->styles()
                                    ->add($this->slug . '-css', $this->uri . '/css/module.css');

    if(file_exists($this->path . '/js/module.js'))
      \cw\wp\Assets::getInstance()->scripts()
                                    ->add($this->slug . '-js', $this->uri . '/js/module.js');
  }

  public function isModuleInPost($postForTest = null){
    if($post === null){
      global $post;
      $postForTest = $post;
    }

    if(!isset($postForTest->post_content)
    || !is_singular()
    || !has_shortcode($postForTest->post_content, $this->slug))
      return false;

    return true;
  }

  public function instanceAttributes($onOffToBool = true){
    $attributes = new \cw\php\core\ArrayAsObject($this->shortcode_atts);

    if($onOffToBool){
      foreach($attributes as $key => &$value){
        if($value === 'on') $value = true;
        if($value === 'off') $value = false;
      };
    }
    return $attributes;
  }

  protected function moduleDisplayName(){
    $tmp = explode('-', $this->main_css_class);
    $tmp = array_map('ucfirst', $tmp);
    return implode(' ', $tmp);
  }

  protected function moduleFolderName(){
    return array_pop(explode('/', $this->path));
  }

  protected function addGroup($id, $name=null){
    $group = new \cw\divi\module\FieldGroup($this, $id, $name);
    $this->groups[$id] = $group;
    return $group;
  }

  protected function addDefaultFields() {
    $group = $this->addGroup('visibility', 'Sichtbarkeit')
                  ->priority(100)
                  ->tabCustomCss();

    $group->addField('disabled_on')
          ->label(esc_html__( 'Disable on', 'et_builder' ))
          ->typeText('geben Sie eine Frage ein!')
          ->description(esc_html__( 'This will disable the module on selected devices', 'et_builder' ))
          ->configurationOption()
          ->typeMultipleCheckboxes([
            'phone'   => esc_html__( 'Phone', 'et_builder' ),
            'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
            'desktop' => esc_html__( 'Desktop', 'et_builder' ),
          ])
          ->additionalAttribute('disable_on');

    $group = $this->addGroup('admin_label', 'Admin Label')
                  ->priority(100)
                  ->tabGeneral();

    $group->addField('admin_label')
          ->label(esc_html__( 'Admin Label', 'et_builder' ))
          ->typeText()
          ->description(esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ));


    $group = $this->addGroup('classes', 'Klassen/IDs')
                  ->tabCustomCss();

    $group->addField('module_id')
          ->label(esc_html__( 'CSS ID', 'et_builder' ))
          ->typeText()
          ->description(esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ))
          ->configurationOption()
          ->optionClass('et_pb_custom_css_regular');

    $group->addField('module_class')
          ->label(esc_html__( 'CSS Class', 'et_builder' ))
          ->typeText()
          ->configurationOption()
          ->optionClass('et_pb_custom_css_regular');

    return $this;
  }

  public function addDefaultAdvancedOptions(){
    $this->advanced_options = array(
      'fonts' => array(
        'text'   => array(
          'label'    => esc_html__( 'Text', 'et_builder' ),
          'css'      => array(
            'line_height' => "{$this->main_css_element} p",
            'color' => "{$this->main_css_element}.et_pb_text",
          ),
          'toggle_slug' => 'text',
        ),
        'header'   => array(
          'label'    => esc_html__( 'Header', 'et_builder' ),
          'css'      => array(
            'main' => "{$this->main_css_element} h1",
          ),
        ),
      ),
      'background' => array(
        'settings' => array(
          'color' => 'alpha',
        ),
      ),
      'border' => array(),
      'custom_margin_padding' => array(
        'css' => array(
          'important' => 'all',
        ),
      ),
    );

    return $this;
  }

  public function get_fields() {
    $fields = [];
    foreach($this->groups as $group){
      foreach($group->fields as $field){
        $fields[$field->id] = $field->config;
        $fields[$field->id]['toggle_slug'] = $group->id;
      }
    }
    return $fields;
  }

  public function beforeRender($callback){
    $this->beforeRenderCallback = $callback;
  }

  public function render($view, $variables=[]){
    if($this->beforeRenderCallback){
      call_user_func_array($this->beforeRenderCallback, [$this, &$variables]);
    }
    return $this->getView()->renderModule($view, $variables);
  }

  public function getView(){
    if($this->view === null)
      $this->view = new \cw\divi\module\View($this);

    return $this->view;
  }
}
