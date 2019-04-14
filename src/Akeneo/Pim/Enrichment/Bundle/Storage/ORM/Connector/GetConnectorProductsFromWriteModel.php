<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use \Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectorProductsFromWriteModel implements Query\GetConnectorProducts
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * TODO: inject activated locales is channel is provided (handler responsibility)
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

            $products[] = ConnectorProduct::fromProductWriteModel($product);
        }

        return $products;
    }

    private function getProduct(string $identifier): ProductInterface
    {
        return $this->productRepository->findOneByIdentifier($identifier);
    }
}
