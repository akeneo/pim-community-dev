<?php
namespace Strixos\IcecatConnectorBundle\Model\Import;

use Akeneo\CatalogBundle\Model\BaseFieldFactory;

use Akeneo\CatalogBundle\Entity\ProductField;

use Akeneo\CatalogBundle\Entity\ProductType;
use Akeneo\CatalogBundle\Model\Doctrine\ProductType as ProductTypeService;

use \XMLReader as XMLReader;

/**
 * Import product data from an icecat XML file
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductImportDataFromXml extends DataImport
{
    /**
     * (non-PHPdoc)
     * @see \Strixos\IcecatConnectorBundle\Model\Import\DataImport::process()
     */
    public function process($xmlFile)
    {
        // read xml document and parse to product entity
        if (!file_exists($xmlFile)) {
            throw new \Exception('xml file non-existent');
        }
        
        $stringXml = file_get_contents($xmlFile);
        $this->_parseXml($stringXml);
        $this->_checkResponse();
        $this->_parseBaseData();
        $this->_parseFeatures();
        
        
        
        $prodData = $this->getProductData();
        $prodFeat = $this->getProductFeatures();
         
        //var_dump($prodData);
        //var_dump($prodFeat);
        
        
        // 2) --> create type
        $typeCode = ProductType::createCode($prodData['vendorId'], $prodData['vendorName'],
                $prodData['CategoryId'], $prodData['CategoryName']);
        
        // if not exists, create a new type
        $type = new ProductTypeService($this->entityManager);
        $return = $type->find($typeCode);
        if (!$return) {
            $type->create($typeCode);
        }
        
        // add all fields of prodData as general fields
        $productFieldCodeToValues = array();
        $generalGroupCode = 'General';
        foreach ($prodData as $field => $value) {
            if ($field != 'id') {
                $fieldCode = ProductField::createCode($prodData['vendorId'], $prodData['CategoryId'], $field);
                if (!$type->getField($fieldCode)) {
                    $type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $generalGroupCode);
                }
                $productFieldCodeToValues[$fieldCode]= $value;
            }
        }
        
        // create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {
            foreach ($featData as $featName => $fieldData) {
                $groupCode = $featId.'-'.strtolower(str_replace(' ', '', $featName));
                foreach ($fieldData as $fieldName => $value) {
                    $fieldCode = $featId.'-'.strtolower(str_replace(' ', '', $fieldName));
                    if (!$type->getField($fieldCode)) {
                        $type->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $groupCode);
                    }
                    $productFieldCodeToValues[$fieldCode]= $value;
                }
            }
        }
        
        // save type
        $type->persist();
        $type->flush();
        
        // 3) ----- create product
        $product = $type->newProductInstance();
        
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
}