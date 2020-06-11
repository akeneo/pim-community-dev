<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentedErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documented\MessageParameterTypes;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownCategoryException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;

class UnknownCategoryExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('category', 'category_code', self::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UnknownCategoryException::class);
    }

    function it_is_an_invalid_property_exception()
    {
        $this->shouldHaveType(InvalidPropertyException::class);
    }

    function it_is_a_domain_error()
    {
        $this->shouldImplement(DomainErrorInterface::class);
    }

    function it_is_has_a_templated_error_message()
    {
        $this->shouldImplement(TemplatedErrorMessageInterface::class);
    }

    function it_is_a_documented_error()
    {
        $this->shouldImplement(DocumentedErrorInterface::class);
    }

    function it_returns_a_message()
    {
        $this->getMessage()->shouldReturn('The category_code category does not exist in your PIM.');
    }

    function it_returns_a_message_template_and_parameters()
    {
        $templatedErrorMessage = $this->getTemplatedErrorMessage();
        $templatedErrorMessage->shouldBeAnInstanceOf(TemplatedErrorMessage::class);
        $templatedErrorMessage->__toString()
            ->shouldReturn('The category_code category does not exist in your PIM.');
    }

    function it_provides_a_documentation()
    {
        $documentation = $this->getDocumentation();
        $documentation->shouldBeAnInstanceOf(DocumentationCollection::class);
        $documentation->normalize()->shouldReturn([
            [
                'message' => 'Please check your {categories_settings}.',
                'parameters' => [
                    'categories_settings' => [
                        'type' => MessageParameterTypes::ROUTE,
                        'route' => 'pim_enrich_categorytree_index',
                        'routeParameters' => [],
                        'title' => 'Categories settings',
                    ],
                ]
            ],
            [
                'message' => 'More information about catalogs and categories: {what_is_a_category} {categorize_a_product}.',
                'parameters' => [
                    'what_is_a_category' => [
                        'type' => MessageParameterTypes::HREF,
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/what-is-a-category.html',
                        'title' => 'What is a category?',
                    ],
                    'categorize_a_product' => [
                        'type' => MessageParameterTypes::HREF,
                        'href' => 'https://help.akeneo.com/pim/serenity/articles/categorize-a-product.html',
                        'title' => 'Categorize a product',
                    ],
                ]
            ]
        ]);
    }
}
