<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectorProductsFromWriteModel
{
    /** @var GetWorkflowStatusForProduct */
    private $getWorkflowStatusForProduct;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    public function __construct(
        GetWorkflowStatusForProduct $getWorkflowStatusForProduct,
        AttributeRepositoryInterface $attributeRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->getWorkflowStatusForProduct = $getWorkflowStatusForProduct;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * TODO: inject activated locales if channel is provided (handler responsibility)
     * TODO: to test
     *
     * {@inheritdoc}
     */
    public function fromProductIdentifiers(
        array $identifiers,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): array
    {
        $identifierAttributeCode = $this->attributeRepository->getIdentifierCode();

        $products = [];
        foreach ($identifiers as $identifier) {
            $product = $this->getProduct($identifier);
            $values = $product->getValues()->filter(function(ValueInterface $value) use ($attributesToFilterOn, $channelToFilterOn, $localesToFilterOn) {
                $isAttributeToKeep = null === $attributesToFilterOn || in_array($value->getAttributeCode(), $attributesToFilterOn);
                $isChannelToKeep = null === $channelToFilterOn || !$value->isScopable() || $value->getScopeCode() === $channelToFilterOn;
                $isLocaleToKeep = null === $localesToFilterOn || !$value->isLocalizable() || in_array($value->getLocaleCode(), $localesToFilterOn);

                return  $isAttributeToKeep && $isChannelToKeep && $isLocaleToKeep;
            });
            $values->removeByAttributeCode($identifierAttributeCode);
            $product->setValues($values);

            $products[] = ConnectorProduct::fromProductWriteModel(
                $product,
                ['workflow_status' => $this->getWorkflowStatusForProduct->fromProduct($product)]
            );
        }

        return $products;
    }



    private function getProduct(string $identifier): ProductInterface
    {
        return $this->productRepository->findOneByIdentifier($identifier);
    }
}
