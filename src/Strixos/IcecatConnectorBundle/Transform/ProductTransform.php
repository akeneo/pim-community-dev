<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Strixos\IcecatConnectorBundle\Entity\Product;
use Strixos\IcecatConnectorBundle\Load\EntityLoad;
use \XMLReader;

use Akeneo\CatalogBundle\Model\BaseFieldFactory;

use Akeneo\CatalogBundle\Entity\ProductField;

use Akeneo\CatalogBundle\Entity\ProductType;
use Akeneo\CatalogBundle\Model\Doctrine\ProductType as ProductTypeService;

/**
 * Aims to transform suppliers xml file to csv file
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : MAKE interfaces to implements xml to csv, xml to php, csv to php, etc.
 */
class ProductTransform extends IcecatTransform
{
    const PREFIX = 'icecat';

    protected $type;

    /**
     * Constructor
     * @param SupplierLoader $loader
     */
    public function __construct($srv)
    {
    	$this->type = $srv;
//         $this->entityManager = $em;
//         $this->documentManager = $dm;
        //$this->loader = new EntityLoad($this->entityManager);
    }

    /**
     * Transform xml file to csv
     *
     * @param string $xmlFile
     * @param string $csvFile
     */
    public function process($filePath)
    {
    // read xml document and parse to product entity
        if (!file_exists($filePath)) {
            throw new \Exception('xml file non-existent');
        }

        $stringXml = file_get_contents($filePath);
        $this->_parseXml($stringXml);
        $this->_checkResponse();
        $this->_parseBaseData();
        $this->_parseFeatures();


        $prodData = $this->getProductData();
        $prodFeat = $this->getProductFeatures();

        //$this->printArray($prodData);

        //var_dump($prodData);
        //var_dump($prodFeat);

        $type = $this->type;

        // 2) --> create type
        $typeCode = ProductType::createCode(self::PREFIX, $prodData['vendorId'], $prodData['CategoryId']);

        // if not exists, create a new type
//         $type = new ProductTypeService($this->entityManager);
        $return = $type->find($typeCode);
        if (!$return) {
            $type->create($typeCode);
        }

        // add all fields of prodData as general fields
        $productFieldCodeToValues = array();
        $generalGroupCode = 'General';
        foreach ($prodData as $field => $value) {
            if ($field != 'id') {
                $fieldCode = ProductField::createCode(self::PREFIX, $prodData['vendorId'], $prodData['CategoryId']);
//                 if (!$type->getField($fieldCode)) {
                    $type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $generalGroupCode, $field);
//                     $productFieldCodeToValues[$fieldCode]= $value;
//                 }
                $productFieldCodeToValues[$fieldCode]= $value;
            }
        }

        // create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {
            foreach ($featData as $featName => $fieldData) {
                $groupCode = $featId.'-'.strtolower(str_replace(' ', '', $featName));
                foreach ($fieldData as $fieldName => $value) {
//                     var_dump($fieldData);
                    $fieldCode = ProductField::createCode(self::PREFIX, $prodData['vendorId'], $featId);
//                     if (!$type->getField($fieldCode)) {
                        $type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $groupCode, $fieldName);
//                         $productFieldCodeToValues[$fieldCode]= $value;
//                     }
                    $productFieldCodeToValues[$fieldCode]= $value;
                }
            }
        }

        // save type
        $type->persist();
        $type->flush();

        // 3) ----- create product
        $product = $type->newProductInstance();
//         echo '<br />product field code to values<br />';
//         var_dump($productFieldCodeToValues);

        // set product values
        foreach ($productFieldCodeToValues as $fieldCode => $value) {
            $product->setValue($fieldCode, $value);
        }

        // save
        $product->persist();
        $product->flush();
    }

    /**
     * Parse xml response
     * @param string $stringXml
     * @return boolean
     */
    protected function _parseXml($stringXml)
    {
        libxml_use_internal_errors(true);
        $this->_simpleDoc = simplexml_load_string($stringXml);
        if ($this->_simpleDoc) {
            return true;
        }
        $this->_simpleDoc = simplexml_load_string(utf8_encode($stringXml));
        if ($this->_simpleDoc) {
            return true;
        }
        return false;
    }

    /**
     * Check Icecat response content
     * @return boolean
     */
    protected function _checkResponse()
    {
        // TODO to raise authentication error or product with no detailled data
        return true;
    }

    /**
     * Parse base product data
     */
    protected function _parseBaseData()
    {
        // get product data
        $productTag = $this->_simpleDoc->Product;
        $this->_productData['id']       = (string)$productTag['Prod_id'];
        $this->_productData['name']     = (string)$productTag['Name'];
        $this->_productData['HighPic']  = (string)$productTag['HighPic'];
        $this->_productData['LowPic']   = (string)$productTag['HighPic'];
        $this->_productData['ThumbPic'] = (string)$productTag['ThumbPic'];
        /* TODO : deal with image id and size ?
         $this->_productData['HighPicHeight'] = (string)$productTag['HighPicHeight'];
        $this->_productData['HighPicSize'] = (string)$productTag['HighPicSize'];
        $this->_productData['HighPicWidth'] = (string)$productTag['HighPicWidth'];
        */

        // TODO deal with other provided product data

        // get vendor data
        $supplierTag = $productTag->Supplier;
        $this->_productData['vendorId']   = (string) $supplierTag['ID'];
        $this->_productData['vendorName'] = (string) $supplierTag['Name'];
        // get summary description data
        $summaryTag = $productTag->SummaryDescription;
        $this->_productData['ShortDescription'] = (string) $productTag->SummaryDescription->ShortSummaryDescription;
        $this->_productData['LongDescription']  = (string) $productTag->SummaryDescription->LongSummaryDescription;

        // get category data
        $categoryTag = $productTag->Category;
        $this->_productData['CategoryId']   = (string) $categoryTag['ID'];
        $this->_productData['CategoryName'] = (string) $categoryTag->Name['Value'];

        // get category feature group id
        $this->_productData['CategoryFeaturesGroupId']   = (string) $productTag->CategoryFeatureGroup['ID'];
    }

    /**
     * Parse base product data
     */
    protected function _parseFeatures()
    {
        $descriptionArray = array();
        $specGroups = $this->_simpleDoc->Product->CategoryFeatureGroup;
        $specFeatures = $this->_simpleDoc->Product->ProductFeature;
        foreach ($specFeatures as $feature) {
            $id = (int)$feature['CategoryFeatureGroup_ID'];
            $featureText = (string) $feature["Presentation_Value"];
            $featureName = (string) $feature->Feature->Name["Value"];
            foreach ($specGroups as $group) {
                $groupId = (int)$group["ID"];
                if ($groupId == $id) {
                    $groupName = (string) $group->FeatureGroup->Name["Value"];
                    $rating = (int)$group['No'];
                    $descriptionArray[$rating][$groupName][$featureName] = $featureText;
                    break;
                }
            }
        }
        krsort($descriptionArray);
        $this->_productFeatures = $descriptionArray;
    }

    /**
     * Get product data
     * @return Array:
     */
    public function getProductData()
    {
        return $this->_productData;
    }

    /**
     * Get product features
     * @return Array
     */
    public function getProductFeatures()
    {
        return $this->_productFeatures;
    }

    private function printArray($tab)
    {
    	echo '<br />Print array --> length : '. count($tab) .'<br />';
    	foreach ($tab as $key => $value) {
    		echo $key .' => '. $value .'<br />';
    	}
    	echo '<br />';
    }
}
