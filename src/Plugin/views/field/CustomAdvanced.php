<?php

namespace Drupal\views_addons\Plugin\views\field;

use Drupal\views\Plugin\views\field\Custom;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_addons_custom_advanced")
 */
class CustomAdvanced extends Custom {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['additional_tags'] = ['default' => 'svg g circle text'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['additional_tags'] = [
      '#title' => $this->t('Set additional allowed tags'),
      '#type' => 'textfield',
      '#default_value' => $this->options['additional_tags'] ? $this->options['additional_tags'] : 'svg g circle text',
      '#help' => $this->t('List tags separated by space. (for example: "svg g circle text")'),
    ];

  }

  /**
   * {@inheritdoc}
   */
  protected function viewsTokenReplace($text, $tokens) {

    $adminTags = Xss::getAdminTagList();
    $customTags = explode(' ', $this->options['additional_tags']);
    if (is_array($customTags)) {
      $allowedTags = array_merge($adminTags, $customTags);
    }
    else {
      $allowedTags = $adminTags;
    }

    // Prepare the allowed tags string for strip_tags().
    $allowedTagsString = '';
    foreach ($allowedTags as $key => $tag) {
      $allowedTagsString .= '<' . $tag . '>';
    }

    // No need to run strip_tags on an empty string.
    if (!strlen($text)) {
      return '';
    }

    // If there are no tokens, we can just strip the tags and return.
    if (empty($tokens)) {
      return strip_tags($text, $allowedTagsString);
    }

    // This foreach loop is copied from PluginBase.php.
    $twig_tokens = [];
    foreach ($tokens as $token => $replacement) {
      if (strpos($token, '{{') !== FALSE) {
        // Twig wants a token replacement array stripped of curly-brackets.
        $token = trim(str_replace(['{{', '}}'], '', $token));
      }

      if (strpos($token, '.') === FALSE) {
        assert(preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $token) === 1, 'Tokens need to be valid Twig variables.');
        $twig_tokens[$token] = $replacement;
      }
      else {
        $parts = explode('.', $token);
        $top = array_shift($parts);
        assert(preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $top) === 1, 'Tokens need to be valid Twig variables.');
        $token_array = [array_pop($parts) => $replacement];
        foreach (array_reverse($parts) as $key) {
          assert(is_numeric($key) || preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $key) === 1, 'Tokens need to be valid Twig variables.');
          $token_array = [$key => $token_array];
        }
        if (!isset($twig_tokens[$top])) {
          $twig_tokens[$top] = [];
        }
        $twig_tokens[$top] += $token_array;
      }
    }

    // Copiend and altered from PluginBase.php to not filter additional tags.
    if ($twig_tokens) {
      $build = [
        '#type' => 'inline_template',
        '#template' => $text,
        '#context' => $twig_tokens,
        '#post_render' => [
          function ($children, $elements) use ($allowedTagsString) {
            return strip_tags($children, $allowedTagsString);
          },
        ],
      ];

      return (string) $this->getRenderer()->renderPlain($build);
    }
    else {
      return strip_tags($text, $allowedTagsString);
    }
  }

}
