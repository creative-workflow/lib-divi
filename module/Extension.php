<?php

namespace cw\divi\module;

class Extension extends \ET_Builder_Module {
  protected $groups     = [];
  protected $view       = null;
  protected $uri        = null;
  protected $attributes = null;
  public    $path       = null;
  public    $assets     = null;
  protected $beforeRenderCallback = [];
  protected $beforeInstanceAttributesCallback = [];
  protected $beforeShortcodeCallback = [];
  public static $css = '';

  public function __construct($path = null){
    parent::__construct();
    $this->path = $path;
    $this->uri  = '/modules/' . $this->moduleFolderName();
    $this->assets = \cw\wp\Assets::getInstance();
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
    ]
  ];

  protected function _add_link_options_fields(){}
  # protected function _add_background_fields(){}
  # protected function _add_font_fields(){}
  protected function _add_text_fields(){}
  protected function _add_borders_fields(){}
  protected function _add_max_width_fields(){}
  protected function _add_margin_padding_fields(){}
  # protected function _add_animation_fields(){}
  protected function _add_additional_transition_fields(){}
  protected function _add_filter_fields(){}
  protected function _add_text_shadow_fields(){}
  protected function _add_box_shadow_fields(){}

  public function init($mainCssClass, $diviModuleSlug, $fullWidth = false) {
    $this->main_css_class   = $mainCssClass;
    $this->main_css_element = '%%order_class%%.'.$mainCssClass;
    $this->slug             = 'et_pb_'.$diviModuleSlug;
    $this->name             = $this->moduleDisplayName();

    $this->vb_support       = true;
    $this->fullwidth        = $fullWidth;

    return $this;
  }

  // this is only here for compat
  protected function enqueueShortcodeAssets(){
    if(!$this->isModuleInPost())
      return ;

    if(file_exists($this->path . '/css/module.css'))
      $this->assets->styles()
                   ->add($this->slug . '-css', $this->uri . '/css/module.css');

    if(file_exists($this->path . '/js/module.js'))
      $this->assets->scripts()
                   ->add($this->slug . '-js', $this->uri . '/js/module.js', ['jquery']);
  }

  public function isModuleInPost($postForTest = null){
    if($postForTest === null){
      global $post;
      $postForTest = $post;
    }

    if(!isset($postForTest->post_content)
    || !has_shortcode($postForTest->post_content, $this->slug))
      return false;

    return true;
  }

  public function rawInstanceAttributes($name, $default = null){
    if(!isset($this->props[$name]))
      return $default;

    return $this->props[$name];
  }

  public function instanceAttributes($onOffToBool = true){
    if(count($this->beforeInstanceAttributesCallback)){
      foreach($this->beforeInstanceAttributesCallback as $callback)
        call_user_func_array($callback, [$this, &$this->props]);
    }

    $attributes = new \cw\php\core\ArrayAsObject($this->props);

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
    $tmp = explode('/', $this->path);
    return array_pop($tmp);
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

    $this->addAnimationSettings();

    return $this;
  }

  public function addDefaultAdvancedOptions(){
    $this->advanced_fields = array(
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

  public function addAnimationSettings(){
    $this->settings_modal_toggles['custom_css']['toggles']['animation'] = [
      'title'    => 'Animation',
      'priority' => 90
    ];

    return $this;
  }

  public function shortcode_callback( $atts, $content = null, $function_name ) {
    $module_class            = $this->props['module_class'];
    $module_class            = \ET_Builder_Element::add_module_order_class( $module_class, $function_name );
    $this->props['module_class'] = $module_class;

    if(count($this->beforeShortcodeCallback)){
      foreach($this->beforeShortcodeCallback as $callback)
        call_user_func_array($callback, [&$this->props, &$content, &$function_name]);
    }

    if(method_exists($this, 'callback'))
      return $this->callback($atts, $content, $function_name);
  }

  public function beforeShortcodeCallback($callback){
    $this->beforeShortcodeCallback[] = $callback;
  }

  public function beforeRender($callback){
    $this->beforeRenderCallback[] = $callback;
  }

  public function beforeInstanceAttributes($callback){
    $this->beforeInstanceAttributesCallback[] = $callback;
  }

  public function renderModule($view, $variables=[]){
    if(count($this->beforeRenderCallback)){
      foreach($this->beforeRenderCallback as $callback)
        call_user_func_array($callback, [$this, &$variables]);
    }

    return $this->getView()->renderModule($view, $variables);
  }

  public function getView(){
    if($this->view === null)
      $this->view = new \cw\divi\module\View($this,
        CW_DIVI_MODULES_FOLDER . '/views/module-wrapper.php');

    return $this->view;
  }


  public function addFontSettings($cssElement, $name, $id){
    if(!is_array($this->advanced_fields))
      $this->advanced_fields = [];

    if(!isset($this->advanced_fields['fonts']))
      $this->advanced_fields['fonts'] = [];

    $this->advanced_fields['fonts'][$id] = [
      'label'    => $name,
      'css'      => [
        'main' => "{$this->main_css_element} {$cssElement}",
        // 'plugin_main' => "{$this->main_css_element} {$cssElement}",
        // 'line_height' => "{$this->main_css_element} {$cssElement}",
      ],
    ];

    // echo '<pre>';
    // var_dump($this->module->advanced_fields); die();

    return $this;
  }
}
