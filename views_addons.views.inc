<?php

/**
 * @file
 * Using the Views API to create a new field.
 */

/**
 * Implements hook_views_data_alter().
 */
function views_addons_views_data_alter(array &$data) {
  foreach ($data as $key => $values) {
    $data['views']['views_addons_custom_advanced'] = [
      'title' => t('Advanced Custom Text'),
      'field' => [
        'title' => t('Advanced Custom Text'),
        'help' => t('Custom Text with altered filtering of Tags.'),
        'id' => 'views_addons_custom_advanced',
      ],
    ];
  }
}
