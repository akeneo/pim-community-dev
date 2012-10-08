<?php
namespace Strixos\IcecatConnectorBundle\Model;

/**
 *
 * @author    Nicolas Dupont @ Strixos
 * @copyright Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductLoader
{

    CONST BASE_URL = 'http://data.Icecat.biz/xml_s3/xml_server3.cgi';

    protected $_simpleDoc       = null;
    protected $_productData     = array();
    protected $_productFeatures = array();

    /**
     * Fetch one product from icecat
     *
     * @param string $prodId
     * @param string $vendor
     * @param string $locale
     */
    public function load($prodId, $vendor, $locale)
    {
        $url = self::BASE_URL.'?prod_id='.$prodId.';vendor='.$vendor.';lang='.$locale.';output=productxml';
        $stringXml = $this->_loadXmlContent($url);

        $this->_parseXml($stringXml);
        $this->_checkResponse();
        $this->_parseBaseData();
        $this->_parseFeatures();

        // TODO deal with some cache or singleton pattern ?
    }

    /**
     * Get xml product content
     *
     * @param string $url
     * @throws Exception
     * @return string
     */
    protected function _loadXmlContent($url)
    {
        // use curl to get xml product content with basic authentication
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, false);
        curl_setopt($c, CURLOPT_USERPWD, BaseExtractor::AUTH_LOGIN.':'.BaseExtractor::AUTH_PASSWORD);
        $output = curl_exec($c);
        // deal with curl exception
        if ($output === false) {
            throw new Exception('Curl Error : '.curl_error($c));
        }
        curl_close($c);

        echo $output;

        return $output;
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