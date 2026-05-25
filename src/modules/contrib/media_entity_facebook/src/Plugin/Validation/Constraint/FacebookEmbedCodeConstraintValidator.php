<?php

namespace Drupal\media_entity_facebook\Plugin\Validation\Constraint;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\media_entity_facebook\Plugin\media\Source\Facebook;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the FacebookEmbedCode constraint.
 */
class FacebookEmbedCodeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $data = NULL;
    if (is_string($value)) {
      $data = $value;
    }
    elseif ($value instanceof FieldItemListInterface) {
      $property = $value->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
      if ($property) {
        $data = $value->$property;
      }
    }
    if ($data) {
      $post_url = Facebook::parseFacebookEmbedField($data);
      if ($post_url === FALSE) {
        $this->context->addViolation($constraint->message);
      }
    }
    else {
      $this->context->addViolation($constraint->message);
    }
  }

}
