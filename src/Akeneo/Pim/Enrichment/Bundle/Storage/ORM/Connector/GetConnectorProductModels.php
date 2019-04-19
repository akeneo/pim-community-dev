<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ORM\Connector;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetMetadataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetConnectorProductModels implements Query\GetConnectorProductModels
{
    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var GetMetadataInterface */
    private $getMetadata;

    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        GetMetadataInterface $getMetadata
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->getMetadata = $getMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $productQueryBuilder,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductModelList {
        $result = $productQueryBuilder->execute();

        $codes = array_map(function (IdentifierResult $identifier) {
            return $identifier->getIdentifier();
        }, iterator_to_array($result));

        return new ConnectorProductModelList(
            $result->count(),
            $this->productModelsFromCode($codes, $attributesToFilterOn, $channelToFilterOn, $localesToFilterOn)
        );
    }

    /**
     * @return array|ConnectorProductModel[]
     */
    private function productModelsFromCode(
        array $codes,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): array {
        $productModels = [];

        foreach ($codes as $code) {
            $productModel = $this->productModelRepository->findOneByIdentifier($code);
            $values = $productModel->getValues()->filter(function (ValueInterface $value) use ($attributesToFilterOn, $channelToFilterOn, $localesToFilterOn) {
                $isAttributeToKeep = null === $attributesToFilterOn || in_array($value->getAttributeCode(), $attributesToFilterOn);
                $isChannelToKeep = null === $channelToFilterOn || !$value->isScopable() || $value->getScopeCode() === $channelToFilterOn;
                $isLocaleToKeep = null === $localesToFilterOn || !$value->isLocalizable() || in_array($value->getLocaleCode(), $localesToFilterOn);

                return $isAttributeToKeep && $isChannelToKeep && $isLocaleToKeep;
            });
            $productModel->setValues($values);
            $productModels[] = ConnectorProductModel::fromWriteModel($productModel, $this->getMetadata->forProductModel($productModel));
        }

        return $productModels;
    }
}
