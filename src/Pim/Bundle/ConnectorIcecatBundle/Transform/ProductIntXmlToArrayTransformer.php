<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Transform;

use \SimpleXMLElement;
/**
 * Aims to transform xml product data to array
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductIntXmlToArrayTransformer implements TransformInterface
{
    /**
     * Xml element to parse
     * @var SimpleXMLElement
     */
    protected $simpleDoc;

    /**
     * Get product base data
     * @var array
     */
    protected $productBaseData;

    /**
     * Get product category information
     * @var array
     */
    protected $productCategory;

    /**
     * Get product groups
     * @array
     */
    protected $productGroups;

    /**
     * Get product features
     * @var array
     */
    protected $productFeatures;

    /**
     * Array combining locale and icecat lang id
     * @staticvar array
     */
    protected static $langs = array('en_US' => 1, 'fr_FR' => 3);

    /**
     * Constructor
     * @param SimpleXMLElement $simpleDoc
     */
    public function __construct(SimpleXMLElement $simpleDoc)
    {
        $this->simpleDoc = $simpleDoc;
    }

    /**
     * {@inheritdoc}
     */
    public function transform()
    {
        $this->parseBaseData($this->simpleDoc);
        $this->parseCategory($this->simpleDoc);
        $this->parseGroups($this->simpleDoc);
        $this->parseFeatures($this->simpleDoc);

        return array(
            'basedata'              => $this->productBaseData,
            'category'              => $this->productCategory,
            'categoryfeaturegroups' => $this->productGroups,
            'productfeatures'       => $this->productFeatures
        );
    }

    /**
     * Parse base product data
     * @param SimpleXMLElement $simpleDoc
     */
    protected function parseBaseData(SimpleXMLElement $simpleDoc)
    {
        // get product data
        $productTag = $simpleDoc->Product;
        $this->productBaseData['id']       = (string) $productTag['Prod_id'];
        $this->productBaseData['name']     = (string) $productTag['Name'];
        $this->productBaseData['HighPic']  = (string) $productTag['HighPic'];
        $this->productBaseData['LowPic']   = (string) $productTag['LowPic'];
        $this->productBaseData['ThumbPic'] = (string) $productTag['ThumbPic'];

        // get vendor data
        $supplierTag = $productTag->Supplier;
        $this->productBaseData['vendorId']   = (string) $supplierTag['ID'];
        $this->productBaseData['vendorName'] = (string) $supplierTag['Name'];

        // get summary description data
        $summaryTag = $productTag->SummaryDescription;
        $this->productBaseData['ShortDescription'] = (string) $summaryTag->ShortSummaryDescription;
        $this->productBaseData['LongDescription']  = (string) $summaryTag->LongSummaryDescription;
    }

    /**
     * Parse category data
     * @param SimpleXMLElement $simpleDoc
     */
    protected function parseCategory(SimpleXMLElement $simpleDoc)
    {
        $categoryTag = $simpleDoc->Product->Category;
        $this->productCategory = array();
        $this->productCategory['id'] = (string) $categoryTag['ID'];
        $this->productCategory['name'] = array();

        foreach ($categoryTag->Name as $categoryName) {
            $langId = (integer) $categoryName['langid'];
            if (in_array($langId, self::$langs)) {
                $this->productCategory['name'][$langId] = (string) $categoryName['Value'];
            }
        }
    }

    /**
     * Parse groups data
     * @param SimpleXMLElement $simpleDoc
     */
    protected function parseGroups(SimpleXMLElement $simpleDoc)
    {
        $this->productGroups= array();

        foreach ($simpleDoc->Product->CategoryFeatureGroup as $groupTag) {
            $groupId = (integer) $groupTag['ID'];
            $this->productGroups[$groupId] = array();

            foreach ($groupTag->FeatureGroup->Name as $groupName) {
                $langId = (integer) $groupName['langid'];
                if (in_array($langId, self::$langs)) {
                    $this->productGroups[$groupId][$langId] = (string) $groupName['Value'];
                }
            }
        }
    }

    /**
     * Parse base product data
     * @param SimpleXMLElement $simpleDoc
     */
    protected function parseFeatures(SimpleXMLElement $simpleDoc)
    {
        $this->productFeatures = array();

        foreach ($simpleDoc->Product->ProductFeature as $featureTag) {
            $featureId = (integer) $featureTag['ID'];
            $groupId   = (integer) $featureTag['CategoryFeatureGroup_ID'];
            $this->productFeatures[$featureId] = array('CategoryFeatureGroup_ID' => $groupId);
            $this->productFeatures[$featureId]['Value'] = array();

            foreach ($featureTag->Feature->Name as $featureName) {
                $langId = (integer) $featureName['langid'];
                if (in_array($langId, self::$langs)) {
                    $this->productFeatures[$featureId]['Value'][$langId] = (string) $featureName['Value'];
                }
            }
        }
    }
}
