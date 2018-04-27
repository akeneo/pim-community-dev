<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\ProductModel;

use Akeneo\Test\Common\Builder\EntityWithValue\ProductModelBuilder;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelContext implements Context
{
    /** @var InMemoryProductModelRepository */
    private $productModelRepository;

    /** @var ProductModelBuilder */
    private $productModelBuilder;

    /**
     * @param InMemoryProductModelRepository $productModelRepository
     * @param ProductModelBuilder            $productModelBuilder
     */
    public function __construct(
        InMemoryProductModelRepository $productModelRepository,
        ProductModelBuilder $productModelBuilder
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelBuilder = $productModelBuilder;
    }

    /**
     * @param string $code
     * @param string $familyVariant
     *
     * @Given a root product model :code from family variant :familyVariant
     */
    public function createProductModel(
        string $code,
        string $familyVariant
    ) {
        $productModel = $this->productModelBuilder
            ->withCode($code)
            ->withFamilyVariant($familyVariant)
            ->build();

        $this->productModelRepository->save($productModel);
    }

    /**
     * @param string    $code
     * @param string    $parentCode
     * @param TableNode $axisValues
     *
     * @Given a sub product model :code of root product model :parentCode with the following axis values:
     */
    public function createSubProductModel(
        string $code,
        string $parentCode,
        TableNode $axisValues
    ): void {
        $parent = $this->productModelRepository->findOneByIdentifier($parentCode);

        if (null === $parent) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The root product model "%s" does not exist',
                    $parentCode
                )
            );
        }

        $this->productModelBuilder
            ->withCode($code)
            ->withParent($parent->getCode())
            ->withFamilyVariant($parent->getFamilyVariant()->getCode());

        foreach ($axisValues->getHash() as $axisValue) {
            foreach ($axisValue as $attributeCode => $value) {
                $this->productModelBuilder->withValue($attributeCode, $value);
            }
        }

        $productModel = $this->productModelBuilder->build();

        $this->productModelRepository->save($productModel);
    }
}
