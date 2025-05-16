<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog\Context;

use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetAttributeTypes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * Use this context to create products
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCreation implements Context
{
    private const IDENTIFIER_ATTRIBUTE = 'sku';

    public function __construct(
        private SaverInterface $attributeSaver,
        private Builder\Product $productBuilder,
        private InMemoryProductRepository $productRepository,
        private EntityBuilder $attributeBuilder,
        private AttributeRepositoryInterface $attributeRepository,
        private EntityBuilder $attributeGroupBuilder,
        private AttributeGroupRepositoryInterface $attributeGroupRepository,
        private InMemoryGetAttributeTypes $getAttributeTypes,
    ) {
    }

    /**
     * @Given a product with an identifier :identifier
     */
    public function aProductWithAnIdentifier(string $identifier): void
    {
        $product = $this->productBuilder->withIdentifier($identifier)->build();
        $this->productRepository->save($product);
    }

    /**
     * @Given a product with the following values:
     */
    public function aProductWithValues(TableNode $table): void
    {
        $this->productBuilder->init();
        foreach ($table as $row) {
            if (isset($row['json_data']) && '' !== $row['json_data']) {
                $data = \json_decode($row['json_data'], true);
            } else {
                $data = $row['data'];
                if (preg_match('/,/', $data)) {
                    $data = explode(',', $row['data']);
                }
            }

            $this->productBuilder->withValue($row['attribute'], $data, $row['locale'] ?? '', $row['scope'] ?? '');
        }

        $this->productRepository->save($this->productBuilder->build());
    }

    /**
     * @Given a catalog with the attribute :identifierAttributeCode as product identifier
     */
    public function aCatalogWithTheAttributeAsProductIdentifier(string $identifierAttributeCode)
    {
        $attributeGroup = $this->attributeGroupBuilder->build([
            'code' => 'other'
        ], true);
        $this->attributeGroupRepository->save($attributeGroup);
        $attribute = $this->attributeBuilder->build([
            'code' => self::IDENTIFIER_ATTRIBUTE,
            'group' => 'other',
            'type' => AttributeTypes::IDENTIFIER,
            'useable_as_grid_filter' => true,
        ], true);
        $this->attributeRepository->save($attribute);
        $this->getAttributeTypes->saveAttribute(self::IDENTIFIER_ATTRIBUTE, AttributeTypes::IDENTIFIER);
    }

    /**
     * @Given /^a product in this family$/
     */
    public function aProductInThisFamily()
    {
        $product = $this->productBuilder
            ->withIdentifier('my_product')
            ->withFamily('my_family')
            ->build(false);

        $this->productRepository->save($product);
    }
}
