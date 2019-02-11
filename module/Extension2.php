<?php

namespace cw\divi\module;

class Extension2 extends \ET_Builder_Module {
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
    $this->path = $path;
    $this->uri  = '/modules/' . $this->moduleFolderName();
    $this->assets = \cw\wp\Assets::getInstance();
    parent::__construct();
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
  protected function _add_background_fields(){}
  protected function _add_text_fields(){}
  protected function _add_animation_fields(){}
  protected function _add_additional_transition_fields(){}
  protected function _add_filter_fields(){}
  protected function _add_text_shadow_fields(){}

  public $useBoxShadow=false;
  protected function _add_box_shadow_fields(){
    if($this->useBoxShadow)
      \ET_Builder_Module::_add_box_shadow_fields();
  }
  public $useBorder=false;
  protected function _add_borders_fields(){
    if($this->useBorder)
      \ET_Builder_Module::_add_borders_fields();
  }
  public $useFonts=false;
  protected function _add_font_fields(){
    if($this->useFonts)
      \ET_Builder_Module::_add_font_fields();
  }
  public $useMaxWidth=false;
  protected function _add_max_width_fields(){
    if($this->useMaxWidth)
      \ET_Builder_Module::_add_max_width_fields();
  }
  public $useMarginOrPadding=false;
  protected function _add_margin_padding_fields(){
    if($this->useMarginOrPadding)
      \ET_Builder_Module::_add_margin_padding_fields();
  }

  public function init($mainCssClass, $diviModuleSlug = null, $fullWidth = false) {
    if($diviModuleSlug === null)
      $diviModuleSlug = $mainCssClass;

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

  protected function addInfoGroup($headline, $content = null){
    if($content === null){
      $content = $headline;
      $headline = '';
    }

    $group = new \cw\divi\module\FieldGroup($this, 'info_box', '&#8520; '.$headline.'');
    $this->groups['info_box'] = $group;
    $group->addField('info_box')->typeInfo($content, $cssClass='info-box');
    return $group;
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

  public function addCustomCss($label, $slug, $selector, $addHover = true, $spaceBeforeSelector = true){
    if($spaceBeforeSelector)
      $selector = ' '.$selector;

    $this->custom_css_fields[$slug] = [
      'label'    => $label,
      'selector' => $selector,
      'no_space_before_selector' => false,
      'tab_slug'        => 'custom_css',
      'toggle_slug'     => 'custom_css',
      'sub_toggle'      => 'column_%s',
      'hover'           => 'tabs'
    ];

    if($addHover){
      $this->custom_css_fields[$slug.'_hover'] = [
        'label'    => $label.' (hover)',
        'selector' => $selector.':hover',
        'no_space_before_selector' => false,
        'tab_slug'        => 'custom_css',
        'toggle_slug'     => 'custom_css',
        'sub_toggle'      => 'column_%s',
        'hover'           => 'tabs'
      ];
    }
    return $this;
  }

  public function addMaxWidth($selector='', $parentSelector = null){
    $this->useMaxWidth = true;
    if($parentSelector === null)
      $parentSelector = $this->main_css_element;

    if(!is_array($this->advanced_fields))
      $this->advanced_fields = [];

    $this->advanced_fields['max_width'] = [
      'css' => [
        'module_alignment' => "$parentSelector $selector"
      ]
    ];
    return $this;
  }

  public function addMarginPadding($marginSelector, $paddingSelector = null, $parentSelector = null){
    $this->useMarginOrPadding = true;
    if($parentSelector === null)
      $parentSelector = $this->main_css_element;

    if(!is_array($this->advanced_fields))
      $this->advanced_fields = [];

    if($paddingSelector === null)
      $paddingSelector = $marginSelector;

    $this->advanced_fields['margin_padding'] = [
      'use_margin' => true,
      // 'custom_margin' =>
      'css' => [
        'margin' => "$parentSelector $marginSelector",
        'padding' => "$parentSelector $paddingSelector"
      ]
    ];
    return $this;
  }

  public function addBorder($slug, $label, $borderSelector, $parentSelector = null){
    $this->useBorder = true;
    if($parentSelector === null)
      $parentSelector = $this->main_css_element;

    if(!is_array($this->advanced_fields))
      $this->advanced_fields = [];

    if(!is_array($this->advanced_fields['borders']))
      $this->advanced_fields['borders'] = [];

    $this->advanced_fields['borders'][$slug] = [
      'label_prefix' => $label,
      'css' => [
        'main'  => [
          'border_radii'  => "$parentSelector $borderSelector",
          'border_styles' => "$parentSelector $borderSelector",
        ]
      ]
    ];
    return $this;
  }

  public function addBoxShadow($slug, $label, $selector, $parentSelector = null){
    $this->useBoxShadow = true;
    if($parentSelector === null)
      $parentSelector = $this->main_css_element;

    if(!is_array($this->advanced_fields))
      $this->advanced_fields = [];

    if(!isset($this->advanced_fields['box_shadow']))
      $this->advanced_fields['box_shadow'] = [];

    $this->advanced_fields['box_shadow'][$slug] = [
      'label'    => $label,
      'css'      => [
        'main' => "$parentSelector $selector"
      ],
    ];
    return $this;
  }


  public function addFontSettings($slug, $label, $cssElement, $parentSelector = null, $onlyShow = null){
    $this->useFonts = true;
    if($parentSelector === null)
      $parentSelector = $this->main_css_element;

    if(!is_array($this->advanced_fields))
      $this->advanced_fields = [];

    if(!isset($this->advanced_fields['fonts']))
      $this->advanced_fields['fonts'] = [];

    $this->advanced_fields['fonts'][$slug] = [
      'label'    => $label,
      'css'      => [
        'main' => "$parentSelector $cssElement"
      ],
    ];

    return $this;
  }

  public function addButton($slug, $label, $selector, $wrapperSelector=null, $parentSelector = null){
    if($parentSelector === null)
      $parentSelector = $this->main_css_element;

    if(!is_array($this->advanced_fields))
      $this->advanced_fields = [];

    if(!isset($this->advanced_fields['button']))
      $this->advanced_fields['button'] = [];

    $this->advanced_fields['button'][$slug] = [
      'label' => $label,
      'css' => [
        'main' => "$parentSelector $selector",
        'alignment' => "$parentSelector $selector",
      ],
      'no_rel_attr' => true,
      'use_alignment' => true,
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
}
