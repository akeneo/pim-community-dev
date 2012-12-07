<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductSetToArrayTransformer;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\TransformInterface;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;

/**
 * Aims to transform product data sheet data to catalog product instance
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataSheetArrayToSetTransformer implements TransformInterface
{
    /**
     * @staticvar string
     */
    const PREFIX = 'icecat';

    /**
     * Get product manager service
     * @var \Pim\Bundle\CatalogBundle\Doctrine\ProductManager
     */
    protected $productManager;

    /**
     * Get product template manager service
     * @var \Pim\Bundle\CatalogBundle\Doctrine\$productTemplateManager
     */
    protected $productTemplateManager;

    /**
     * Product data sheet to transform
     * @var IcecatProductDataSheet
     */
    protected $datasheet;

    /**
     * Constructor
     *
     * @param ProductManager         $productManager     product manager
     * @param ProductTemplateManager $productTmplManager product template manager
     * @param IcecatProductDataSheet $datasheet          product datasheet
     */
    public function __construct(ProductManager $productManager, ProductTemplateManager $productTmplManager,
        IcecatProductDataSheet $datasheet)
    {
        $this->productManager = $productManager;
        $this->productTemplateManager = $productTemplateManager;
        $this->datasheet = $datasheet;
    }

    /**
     * {@inheritdoc}
     */
    public function transform()
    {
        $localeIcecat = 1; // en_US

        // get datas
        $allData = json_decode($this->datasheet->getData(), true);
        $categoryData    = $allData['category'];
        $catFeatureData  = $allData['categoryfeaturegroups'];
        $prodFeatureData = $allData['productfeatures'];

        // prepare category data
        $setData = array(
            'id'     => null,
            'code'   => self::PREFIX .'-'. $categoryData['id'],
            'title'  => $categoryData['name'][$localeIcecat],
            'groups' => array()
        );

        // add groups
        foreach ($catFeatureData as $icecatGroupId => $feature) {
            $groupCode = self::PREFIX .'-'. $icecatGroupId;

            $groupData = array(
                'id'    => null,
                'code'  => $groupCode,
                'title' => $feature[$localeIcecat],
                'attributes' => array()
            );

            $setData['groups'][$groupCode] = $groupData;
        }

        // add attributes
        foreach ($prodFeatureData as $icecatAttId => $attribute) {
            // get attribute
            $attCode = self::PREFIX .'-'. $icecatAttId;
            $att = $this->productManager->getAttributeRepository()->findOneByCode($attCode);

            // add attribute to group
            $groupCode = self::PREFIX .'-'. $attribute['CategoryFeatureGroup_ID'];
            $setData['groups'][$groupCode]['attributes'][] = $att->getId();
        }

        // initialize data transformer and transform data to set entity
        $dataTransformer = new ProductSetToArrayTransformer($this->productManager, $this->productTemplateManager);
        $set = $dataTransformer->reverseTransform($setData);

        return $set;
    }

    protected function transformToGroups()
    {

    }

    protected function addAttributesToGroup()
    {

    }
}
