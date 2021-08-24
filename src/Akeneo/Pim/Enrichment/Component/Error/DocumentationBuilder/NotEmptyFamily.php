<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotEmptyFamily as ConstraintNotEmptyFamily;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotEmptyFamily implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if (
            $object instanceof ConstraintViolationInterface
            && $object->getCode() === ConstraintNotEmptyFamily::NOT_EMPTY_FAMILY
        ) {
            return true;
        }
        return false;
    }

    public function buildDocumentation($object): DocumentationCollection
    {
        if (false === $this->support($object)) {
            throw new \InvalidArgumentException('Parameter $object is not supported.');
        }

        return new DocumentationCollection([
            new Documentation(
                'More information about variant products: {product_variant}',
                [
                    'product_variant' => new HrefMessageParameter(
                        'What about products with variants?',
                        'https://help.akeneo.com/pim/serenity/articles/what-about-products-variants.html'
                    ),
                ],
                Documentation::STYLE_INFORMATION
            ),
        ]);
    }
}
