<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder\UnknownCategory;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownCategoryException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UnknownCategorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beAnInstanceOf(UnknownCategory::class);
    }

    function it_is_a_documentation_builder()
    {
        $this->beAnInstanceOf(DocumentationBuilderInterface::class);
    }

    function it_supports_the_error_unknown_category()
    {
        $exception = new UnknownCategoryException('categories', 'category_code', self::class);

        $this->support($exception)->shouldReturn(true);
    }

    function it_does_not_support_other_types_of_error()
    {
        $exception = new \Exception();

        $this->support($exception)->shouldReturn(false);
    }

    function it_builds_the_documentation()
    {
        $exception = new UnknownCategoryException('categories', 'category_code', self::class);

        $documentation = $this->buildDocumentation($exception);

        $documentation->shouldHaveType(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check your {categories_settings}.',
                'parameters' => [
                    'categories_settings' => [
                        'type' => 'route',
                        'route' => 'pim_enrich_categorytree_index',
                        'routeParameters' => [],
                        'title' => 'Categories settings',
                    ],
                ],
                'style' => 'text'
            ],
            [
                'message' => 'More information about catalogs and categories: {what_is_a_category} {categorize_a_product}.',
                'parameters' => [
                    'what_is_a_category' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-a-category.html',
                        'title' => 'What is a category?',
                    ],
                    'categorize_a_product' => [
                        'type' => 'href',
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/categorize-a-product.html',
                        'title' => 'Categorize a product',
                    ],
                ],
                'style' => 'information'
            ],
        ]);
    }

    function it_does_not_build_the_documentation_for_an_unsupported_error(\Exception $exception)
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('buildDocumentation', [$exception]);
    }
}
