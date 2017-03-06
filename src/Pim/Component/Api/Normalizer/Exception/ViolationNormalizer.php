<?php

namespace Pim\Component\Api\Normalizer\Exception;

use Doctrine\Common\Inflector\Inflector;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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
     * @param ConstraintViolationListInterface $violations
     *
     * @return array
     */
    protected function normalizeViolations(ConstraintViolationListInterface $violations)
    {
        $errors = [];

        foreach ($violations as $violation) {
            $error = [
                'field'   => Inflector::tableize($violation->getPropertyPath()),
                'message' => $violation->getMessage()
            ];

            if ($violation->getRoot() instanceof ProductInterface &&
                1 === preg_match('|^values\[(?P<attribute>[a-z0-9-_]+)|i', $violation->getPropertyPath(), $matches)) {
                $error = $this->getProductValuesErrors($violation, $matches['attribute']);
            }

            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Constraints for product values are not displayed correctly.
     * For instance, an error for attribute "a_text" will be displayed like that: "values[a_text-fr_FR-ecommerce].varchar"
     * In the API, the same error will be:
     * [
     *    "field": "values",
     *    "attribute": "a_text",
     *    "locale": "fr_FR",
     *    "scope": "ecommerce",
     *    "message": "..."
     * ]
     *
     * Exception for identifier attribute (which is displayed like "values[sku].varchar"), we will return information like that:
     * [
     *    "field": "identifier",
     *    "message": "..."
     * ]
     *
     * @param ConstraintViolationInterface $violation
     * @param string                       $attributeCode
     *
     * @return array
     */
    protected function getProductValuesErrors(ConstraintViolationInterface $violation, $attributeCode)
    {
        $productValue = $violation->getRoot()->getValues()[$attributeCode];
        $attributeType = $productValue->getAttribute()->getAttributeType();

        if (AttributeTypes::IDENTIFIER === $attributeType) {
            return [
                'field'     => 'identifier',
                'message'   => $violation->getMessage()
            ];
        }

        $error = [
            'field'     => 'values',
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
