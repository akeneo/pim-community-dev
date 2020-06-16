<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\Documentation;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\HrefMessageParameter;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\RouteMessageParameter;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownCategoryException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UnknownCategory implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if ($object instanceof UnknownCategoryException) {
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
                'Please check your {categories_settings}.',
                [
                    'categories_settings' => new RouteMessageParameter(
                        'Categories settings',
                        'pim_enrich_categorytree_index'
                    )
                ],
                Documentation::STYLE_TEXT
            ),
            new Documentation(
                'More information about catalogs and categories: {what_is_a_category} {categorize_a_product}.',
                [
                    'what_is_a_category' => new HrefMessageParameter(
                        'What is a category?',
                        'https://help.akeneo.com/pim/serenity/articles/what-is-a-category.html'
                    ),
                    'categorize_a_product' => new HrefMessageParameter(
                        'Categorize a product',
                        'https://help.akeneo.com/pim/serenity/articles/categorize-a-product.html'
                    )
                ],
                Documentation::STYLE_INFORMATION
            )
        ]);
    }
}
