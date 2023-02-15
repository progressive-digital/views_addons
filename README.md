# views_addons
Additional features for Drupal views.

## Advanced Custom Text
A new field that can be used in views like the Custom Text field, but
without the limitations of the core field. Additional tags can be allowed in
this field in the configuration. This makes svg, picture, source tags and so on
usable which get filtered in the normal Custom Text field provided in the Drupal
core.

## How to include it in your Drupal project
Add the repository to your composer.json like this:
```json
"repositories": [
  {
      "type": "composer",
      "url": "https://packages.drupal.org/8"
  },
  {
    "type": "git",
    "url": "https://github.com/progressive-digital/views_addons.git"
  }
]
```

And then require this module:
```bash
composer require 'progressive-digital/views_addons:^1.0'
```
