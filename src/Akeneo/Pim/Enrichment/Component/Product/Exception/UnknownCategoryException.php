<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\Documented\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentedErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documented\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documented\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnknownCategoryException extends InvalidPropertyException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface,
    DocumentedErrorInterface
{
    /** @var TemplatedErrorMessage */
    private $templatedErrorMessage;

    public function __construct(string $propertyName, string $propertyValue, string $className)
    {
        $this->templatedErrorMessage = new TemplatedErrorMessage(
            'The {category_code} category does not exist in your PIM.',
            ['category_code' => $propertyValue]
        );

        parent::__construct(
            $propertyName,
            $propertyValue,
            $className,
            (string) $this->templatedErrorMessage,
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedErrorMessage;
    }

    public function getDocumentation(): DocumentationCollection
    {
        return new DocumentationCollection([
            new Documentation(
                'Please check your {categories_settings}.',
                [
                    'categories_settings' => new RouteMessageParameter(
                        'Categories settings',
                        'pim_enrich_categorytree_index'
                    )
                ]
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
                ]
            )
        ]);
    }
}
