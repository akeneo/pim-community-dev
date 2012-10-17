<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Akeneo\CatalogBundle\Model\BaseFieldFactory;

/**
 * Aims to transform array product data to catalog product instance
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductArrayToCatalogProductTransformer
{
    const PREFIX = 'icecat';

    /**
     * Get product instance
     * @var Object
     */
    protected $product;

    /**
     * Get product type service
     * @var Service
     */
    protected $type;

    /**
    * Constructor
    * @param Service $loader
    */
    public function __construct($service)
    {
        $this->type = $service;
    }

    /**
    * Transform product array data to product instance
    *
    * @param array $prodData
    * @param array $features
    */
    public function process($prodData, $prodFeat)
    {
        // 2) --> create type
        $typeCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'];

        // if not exists, create a new type
        $return = $this->type->find($typeCode);
        if (!$return) {
            $this->type->create($typeCode);
        }

        // add all fields of prodData as general fields
        $productFieldCodeToValues = array();
        $generalGroupCode = 'General';
        foreach ($prodData as $field => $value) {
            if ($field == 'id') {
                $fieldCode = self::PREFIX.'_source_id';
            } else {
                $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'].'-'.strtolower($field);
            }
            $this->type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $generalGroupCode, $field);
            $productFieldCodeToValues[$fieldCode]= $value;
        }

        // create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {
            foreach ($featData as $featName => $fieldData) {
                $groupCode = $featId.'-'.strtolower(str_replace(' ', '', $featName));
                foreach ($fieldData as $fieldId => $fieldData) {
                    $fieldName = $fieldData['name'];
                    $value = $fieldData['value'];
                    $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$featId.'-'.$fieldId;
                    $this->type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $groupCode, $fieldName);
                    $productFieldCodeToValues[$fieldCode]= $value;
                }
            }
        }

        // save type
        $this->type->persist();
        $this->type->flush();

        // 3) ----- create product
        // TODO get product from icecat_source_id if already exists
        $product = $this->type->newProductInstance();

        // set product values
        foreach ($productFieldCodeToValues as $fieldCode => $value) {
            $product->setValue($fieldCode, $value);
        }

        // save
        $product->persist();
        $product->flush();
    }

    /**
     * Get product instance
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
