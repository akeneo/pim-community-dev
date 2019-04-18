<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use \Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetMetadataInterface;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectorProductsFromWriteModel implements Query\GetConnectorProducts
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var GetMetadataInterface */
    private $getMetadata;

    /**
     * @param IdentifiableObjectRepositoryInterface $productRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        AttributeRepository $attributeRepository,
        GetMetadataInterface $getMetadata
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->getMetadata = $getMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        Query\ProductQueryBuilderInterface $pqb,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $result = $pqb->execute();
        $identifiers = array_map(function (IdentifierResult $identifier) {
            return $identifier->getIdentifier();
        }, iterator_to_array($result));
        $identifierAttributeCode = $this->attributeRepository->getIdentifierCode();

        $products = [];
        foreach ($identifiers as $identifier) {
            $product = $this->getProduct($identifier);
            $values = $product->getValues()->filter(function (ValueInterface $value) use ($attributesToFilterOn, $channelToFilterOn, $localesToFilterOn) {
                $isAttributeToKeep = null === $attributesToFilterOn || in_array($value->getAttributeCode(), $attributesToFilterOn);
                $isChannelToKeep = null === $channelToFilterOn || !$value->isScopable() || $value->getScopeCode() === $channelToFilterOn;
                $isLocaleToKeep = null === $localesToFilterOn || !$value->isLocalizable() || in_array($value->getLocaleCode(), $localesToFilterOn);

                return  $isAttributeToKeep && $isChannelToKeep && $isLocaleToKeep;
            });
            $values->removeByAttributeCode($identifierAttributeCode);
            $product->setValues($values);

            $products[] = ConnectorProduct::fromProductWriteModel($product, $this->getMetadata->forProduct($product));
        }

        return new ConnectorProductList($result->count(), $products);
    }

    private function getProduct(string $identifier): ProductInterface
    {
        return $this->productRepository->findOneByIdentifier($identifier);
    }
}
