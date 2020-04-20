<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\Product\MapProduct;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\Product\MapProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Sets the association field
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationFieldSetter extends AbstractFieldSetter
{
    /**
     * @param array                                 $supportedFields
     */
    public function __construct(
        MapProduct $mapProduct,
        MapProductModel $mapProductModel,
        array $supportedFields
    ) {
        $this->mapProduct = $mapProduct;
        $this->mapProductModel = $mapProductModel;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format :
     * {
     *     "XSELL": {
     *         "groups": ["group1", "group2"],
     *         "products": ["AKN_TS1", "AKN_TSH2"],
     *         "product_models": ["MODEL_AKN_TS1", "MODEL_AKN_TSH2"]
     *     },
     *     "UPSELL": {
     *         "groups": ["group3", "group4"],
     *         "products": ["AKN_TS3", "AKN_TSH4"],
     *         "product_models": ["MODEL_AKN_TS3 "MODEL_AKN_TSH4"]
     *     },
     * }
     */
    public function setFieldData($entity, $field, $data, array $options = [])
    {
        if (!$entity instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected($entity, EntityWithValuesInterface::class);
        }

        $this->checkData($field, $data);
        $this->setAssociations($entity, $data);
    }

    /**
     * Clear only concerned associations (remove groups and products from existing associations)
     *
     * @param EntityWithAssociationsInterface $entity
     * @param array                           $data
     *
     * Expected data input format:
     * {
     *     "XSELL": {
     *         "products": [["identifier" => "AKN_TS1", "quantity" => 12], ["identifier" => "AKN_TSH4", "quantity" => 4]],
     *         "product_models": []
     *     },
     *     "UPSELL": {
     *         "products": [],
     *         "product_models": []
     *     },
     * }
     */
    protected function setAssociations(AbstractProduct $entity, array $data = null)
    {
        if (null === $data) {
            return;
        }

        $productIds = $this->getProductIdsForIdentifiers($data);
        $productModelIds = $this->getProductModelIdsForCodes($data);

        $entity->setQuantifiedAssociationsWithIds($data, $productIds, $productModelIds);
    }

    private function getProductIdsForIdentifiers(array $data)
    {
        $productIdentifiers = array_reduce($data, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['products'] ?? [], 'identifier'));
        }, []);

        return $this->mapProduct->forIdentifiers($productIdentifiers);
    }

    private function getProductModelIdsForCodes(array $data)
    {
        $productModelCodes = array_reduce($data, function (array $carry, array $quantifiedAssociation) {
            return array_merge($carry, array_column($quantifiedAssociation['product_models'] ?? [], 'code'));
        }, []);


        return $this->mapProductModel->forCodes($productModelCodes);
    }

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData($field, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $field,
                static::class,
                $data
            );
        }

        foreach ($data as $assocTypeCode => $items) {
            $assocTypeCode = (string) $assocTypeCode;
            $this->checkAssociationData($field, $data, $assocTypeCode, $items);
        }
    }

    /**
     * @param string $field
     * @param array  $data
     * @param string $assocTypeCode
     * @param mixed  $items
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkAssociationData($field, array $data, $assocTypeCode, $items)
    {
        if (!is_array($items) || !is_string($assocTypeCode) ||
            (!isset($items['products']) && !isset($items['groups']) && !isset($items['product_models']))
        ) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                sprintf('association format is not valid for the association type "%s".', $assocTypeCode),
                static::class,
                $data
            );
        }

        foreach ($items as $type => $itemData) {
            if (!is_array($itemData)) {
                $message = sprintf(
                    'Property "%s" in association "%s" expects an array as data, "%s" given.',
                    $type,
                    $assocTypeCode,
                    gettype($itemData)
                );

                throw new InvalidPropertyTypeException(
                    $type,
                    $itemData,
                    static::class,
                    $message,
                    InvalidPropertyTypeException::ARRAY_EXPECTED_CODE
                );
            }

            $this->checkAssociationItems($field, $assocTypeCode, $data, $itemData);
        }
    }

    /**
     * @param string $field
     * @param string $assocTypeCode
     * @param array  $data
     * @param array  $items
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkAssociationItems($field, $assocTypeCode, array $data, array $items)
    {
        foreach ($items as $item) {
            if (!is_string($item['identifier']) || !is_integer($item['quantity'])) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('association format is not valid for the association type "%s".', $assocTypeCode),
                    static::class,
                    $data
                );
            }
        }
    }
}
