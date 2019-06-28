<?php

namespace Drupal\falcon_thankq\Plugin\Field\FieldWidget;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

/**
 * Source codes field widget.
 *
 * @FieldWidget(
 *   id = "inline_source_code_form",
 *   label = @Translation("Inline Source Codes form"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class InlineSourceCodeForm extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Mainly use parent code to render the form.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Change field label to the one related to source codes.
    $element['entities']['#table_fields']['label']['label'] = $this->t('Source code');

    foreach (Element::children($element['entities']) as $key) {

      // Get Source Code entity.
      /* @var $entity \Drupal\eck\EckEntityInterface */
      $entity = $element['entities'][$key]['#entity'];

      // Grab source code & human readable label.
      $source_code = $entity->field_source_code->value;
      $label = $entity->field_label->value;
      $date = '';

      // Render availability date in the human readable format.
      $availability = $entity->get('field_availability')->getValue();
      if (!empty($availability[0]['value'])) {
        $date .= new FormattableMarkup('from @from ', ['@from' => $availability[0]['value']]);
      }
      if (!empty($availability[0]['end_value'])) {
        $date .= new FormattableMarkup('until @to', ['@to' => $availability[0]['end_value']]);
      }
      $date = $date ? '(' . $date . ')' : '';

      // Replace form's label for the widget with beautiful & helpful output.
      $element['entities'][$key]['#label'] = trim("[$source_code] $label $date");
    }

    return $element;
  }

}
