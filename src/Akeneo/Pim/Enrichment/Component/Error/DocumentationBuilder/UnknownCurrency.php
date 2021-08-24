<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Currency;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnknownCurrency implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if (
            $object instanceof ConstraintViolationInterface
            && $object->getCode() === Currency::CURRENCY
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
                'Please check your {currency_settings}.',
                [
                    'currency_settings' => new RouteMessageParameter(
                        'Currency settings',
                        'pim_enrich_currency_index'
                    )
                ],
                Documentation::STYLE_TEXT
            ),
            new Documentation(
                'More information about price attributes and currencies: {attribute_types} {manage_currency}',
                [
                    'attribute_types' => new HrefMessageParameter(
                        'Akeneo attribute types',
                        'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html#akeneo-attribute-types'
                    ),
                    'manage_currency' => new HrefMessageParameter(
                        'View and enable your currencies',
                        'https://help.akeneo.com/pim/v3/articles/manage-your-currencies.html'
                    ),
                ],
                Documentation::STYLE_INFORMATION
            ),
        ]);
    }
}
