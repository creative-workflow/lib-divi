# creative-workflow/lib-divi
Make Divi module development smart for developers. See module implemenatation here: https://github.com/creative-workflow/wordpress-divi-child-modules/blob/master/hello-world/Module.php


### Setup
```
git submodule add https://github.com/creative-workflow/lib-divi.git ./wordpress/wp-content/themes/child/lib/cw/divi
git submodule add https://github.com/creative-workflow/lib-wp.git ./wordpress/wp-content/themes/child/lib/cw/wp
git submodule add https://github.com/creative-workflow/lib-php.git ./wordpress/wp-content/themes/child/lib/cw/php
git submodule add https://github.com/creative-workflow/lib-sass.git ./wordpress/wp-content/themes/child/lib/cw/sass

git submodule init && git submodule update

git submodule foreach git checkout master
```

### Helper
```php
<?php

cw\divi\Helper::enableEditorForCustomPostTypes();

cw\divi\Helper::enableLibraryForCustomLayouts();

cw\divi\module\Helper::register(
  glob(CW_DIVI_MODULES_FOLDER . '/**/*Module*.php')
);

cw\divi\module\Helper::register(
  glob(CW_DIVI_MODULES_FOLDER . '/*Module*.php')
);
```

### Modules
##### hallo-world/Module.php
```php
<?php

class ModuleHalloWorld extends cw\divi\module\Extension {
  public function init() {
    parent::init('cw-module-hallo-world', 'custom_hallo_world');

    $this->addDefaultFields();

    $group = $this->addGroup('main_module', 'Main')
                  ->tabGeneral();

    $group->addField('headline')
          ->label('Überschrift')
          ->typeText('Überschrift')
          ->addFontSettings('.module-headline');

    $group->addField('headline_tag')
          ->label('Überschrift-Tag')
          ->typeSelect([
            'h1' => 'h1',
            'h2' => 'h2',
            'h3' => 'h3',
            'h4' => 'h4',
            'h5' => 'h5',
            'h6' => 'h6',
            'strong' => 'strong',
            'b' => 'b',
            'div' => 'div'
          ]);

    $group->addField('text')
          ->label('Text')
          ->typeHtml()
          ->addFontSettings('.text');

    $group->addField('image')
          ->label('Bild')
          ->typeUpload()
          ->description('Geben Sie ein Bild an!')
          ->basicOption();

      return $this;
  }

  public function shortcode_callback( $atts, $content = null, $function_name ) {
    $variables = $this->shortcode_atts;
    $variables['text'] = $this->shortcode_content;

    return $this->render(
      'views/module.php',
      $variables
    );
  }
}
new ModuleTherapyMethod(__DIR__);
```

##### hallo-world/views/module.php
```php
<div class="content-wrapper">
  <div class="headline-wrapper">
    <?= $this->tag($headline_tag, $headline, ['class' => 'module-headline']) ?>
  </div>

  <div class="text">
    <?= $text ?>
  </div>
</div>

<?

  if($image)
    echo $this->image($image, ['class' => 'image']);

?>
```

##### hallo-world/css/module.sass
```sass
@import "variables"

@import "mixins/css/css3"
@import "mixins/css/positioning"
@import "mixins/helper/helper"

@import "mixins/grid/mediaqueries"
@import "mixins/grid/grid"

@import "mixins/wordpress/divi"
@import "mixins/wordpress/post"


+custom-divi-module('cw-module-hallo-world')
  .image
    display: none
    +min-width-sm
      +block
      +absolute
      right: -40px
      bottom: 0

  .content-wrapper
    [...]
```
