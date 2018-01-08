<?php

namespace Pim\Component\Api\Normalizer\Exception;

use Doctrine\Common\Inflector\Inflector;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Validator\Constraints\UniqueValue;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Normalize a ViolationHttpException with all errors
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ViolationNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($exception, $format = null, array $context = [])
    {
        $errors = $this->normalizeViolations($exception->getViolations());

        $data = [
            'code'    => $exception->getStatusCode(),
            'message' => $exception->getMessage(),
            'errors'  => $errors
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($exception, $format = null)
    {
        return $exception instanceof ViolationHttpException;
    }

    /**
     * The product field "identifier" introduced during the single storage development (in addition to the "identifier"
     * product value) added a new Length constraint on this property (see the product validation mapping)
     * which is breaking the API.
     *
     * This method does not normalize the "identifier" property to normalize only the constraint regarding the product
     * value (Because its Length max number is dynamic compared to the identifier property).
     *
     * Also, product field "identifier" has a unique entity constraint with the message
     * "The same identifier is already set on another product". The same behavior is ensured thanks to the
     * {@see Pim\Component\Catalog\Validator\Constraints\UniqueValue} constraint, but with a different message.
     * Here we keep only the first violation.
     *
     * TODO: TIP-722 - to revert once the identifier product value is dropped.
     *
     * @param ConstraintViolationListInterface $violations
     *
     * @return array
     */
    protected function normalizeViolations(ConstraintViolationListInterface $violations)
    {
        $errors = [];
        $existingViolation = [];

        foreach ($violations as $violation) {
            $error = [
                'property' => $this->getErrorField($violation),
                'message'  => $violation->getMessage()
            ];

            $propertyPath = $violation->getPropertyPath();
            $violationMessage = $violation->getMessageTemplate();

            if ($violation->getRoot() instanceof EntityWithValuesInterface &&
                1 === preg_match(
                    '|^values\[(?P<attribute>[a-z0-9-_\<\>]+)|i',
                    $violation->getPropertyPath(),
                    $matches
                )
            ) {
                $error = $this->getProductValuesErrors($violation, $matches['attribute']);

                $productValue = $violation->getRoot()->getValues()->getByKey($matches['attribute']);
                $attributeType = $productValue->getAttribute()->getType();

                if (AttributeTypes::IDENTIFIER === $attributeType) {
                    $propertyPath = 'identifier';
                    $uniqueValueConstraint = new UniqueValue();

                    if ($uniqueValueConstraint->message === $violationMessage) {
                        // this is the translation key used in
                        // Pim/Bundle/CatalogBundle/Resources/config/validation/product.yml
                        $violationMessage = 'The same identifier is already set on another product';
                    }
                }
            }

            if ($violation->getRoot() instanceof ChannelInterface && 'category' === $violation->getPropertyPath()) {
                $error['property'] = 'category_tree';
            }

            $key = $propertyPath.$violationMessage;
            if (!array_key_exists($key, $existingViolation)) {
                $errors[] = $error;
            }

            $existingViolation[$key] = true;
        }

        return $errors;
    }

    /**
     * Returns the field concerned by the violation. It must be standard format valid.
     * If a name has been set in the constraint payload it is used, else it fallbacks on a tableized version of the
     * entity property (example: 'metricFamily' -> 'metric_family').
     *
     * @param ConstraintViolationInterface $violation
     *
     * @return string
     */
    protected function getErrorField(ConstraintViolationInterface $violation)
    {
        $constraint = $violation->getConstraint();

        if (null !== $constraint && isset($constraint->payload['standardPropertyName'])) {
            return $constraint->payload['standardPropertyName'];
        }

        return Inflector::tableize($violation->getPropertyPath());
    }

    /**
     * Constraints for product values are not displayed correctly.
     * For instance, an error for attribute "a_text" will be displayed like that:
     *      "values[a_text-fr_FR-ecommerce].text"
     *
     * In the API, the same error will be:
     * [
     *    "field": "values",
     *    "attribute": "a_text",
     *    "locale": "fr_FR",
     *    "scope": "ecommerce",
     *    "message": "..."
     * ]
     *
     * Exception for identifier attribute (which is displayed like "values[sku].text"),
     * we will return information like that:
     * [
     *    "field": "identifier",
     *    "message": "..."
     * ]
     *
     * TODO: TIP-722 To remove once the "identifier" product value is removed from the product value collection.
     *
     * @param ConstraintViolationInterface $violation
     * @param string                       $productValueKey
     *
     * @return array
     */
    protected function getProductValuesErrors(ConstraintViolationInterface $violation, $productValueKey)
    {
        $productValue = $violation->getRoot()->getValues()->getByKey($productValueKey);
        $attributeType = $productValue->getAttribute()->getType();

        if (AttributeTypes::IDENTIFIER === $attributeType) {
            return [
                'property'  => 'identifier',
                'message'   => $violation->getMessage()
            ];
        }

        $error = [
            'property'  => 'values',
            'message'   => $violation->getMessage(),
            'attribute' => $productValue->getAttribute()->getCode(),
            'locale'    => $productValue->getLocale(),
            'scope'     => $productValue->getScope()
        ];

        if (AttributeTypes::PRICE_COLLECTION === $attributeType &&
            null !== $violation->getInvalidValue()->getCurrency()) {
            $error['currency'] = $violation->getInvalidValue()->getCurrency();
        }

        return $error;
    }
}
