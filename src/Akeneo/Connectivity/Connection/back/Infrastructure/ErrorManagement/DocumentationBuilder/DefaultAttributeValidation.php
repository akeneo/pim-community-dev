<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\Documentation;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\HrefMessageParameter;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\RouteMessageParameter;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlank;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotDecimal;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Range;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Regex;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DefaultAttributeValidation implements DocumentationBuilderInterface
{
    const SUPPORTED_CONSTRAINTS_CODES = [
        IsNumeric::IS_NUMERIC,
        NotBlank::IS_BLANK_ERROR,
        Range::INVALID_CHARACTERS_ERROR,
        Range::NOT_IN_RANGE_ERROR,
        Range::TOO_HIGH_ERROR,
        Range::TOO_LOW_ERROR,
        Regex::REGEX_FAILED_ERROR,
        UniqueValue::UNIQUE_VALUE,
        NotDecimal::NOT_DECIMAL,
        IsString::IS_STRING,
        Length::TOO_LONG_ERROR,
    ];

    public function support($object): bool
    {
        if (
            $object instanceof ConstraintViolationInterface
            && in_array($object->getCode(), self::SUPPORTED_CONSTRAINTS_CODES)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param ConstraintViolationInterface $constraintViolation
     */
    public function buildDocumentation($constraintViolation): DocumentationCollection
    {
        if (false === $this->support($constraintViolation)) {
            throw new \InvalidArgumentException('Parameter $constraintViolation is not supported.');
        }

        return new DocumentationCollection([
            new Documentation(
                'More information about attributes and their validation: {manage_attributes} {manage_validation_parameters}',
                [
                    'manage_attributes' => new HrefMessageParameter(
                        'Manage your attributes',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html'
                    ),
                    'manage_validation_parameters' => new HrefMessageParameter(
                        'Manage your validation parameters',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html#add-attributes-validation-parameters'
                    )
                ],
                Documentation::STYLE_INFORMATION
            ),
            new Documentation(
                'Please check your {attribute_settings}.',
                [
                    'attribute_settings' => new RouteMessageParameter(
                        'Attributes settings',
                        'pim_enrich_attribute_index'
                    )
                ],
                Documentation::STYLE_TEXT
            )
        ]);
    }
}
