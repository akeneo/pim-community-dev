<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Product\UniqueProductEntity;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UniqueProductIdentifier implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if (
            $object instanceof ConstraintViolationInterface
            && $object->getCode() === UniqueProductEntity::UNIQUE_PRODUCT_ENTITY
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
                'More information about identifier attributes: {attribute_types}',
                [
                    'attribute_types' => new HrefMessageParameter(
                        'Akeneo attribute types',
                        'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html#akeneo-attribute-types'
                    ),
                ],
                Documentation::STYLE_INFORMATION
            ),
        ]);
    }
}
