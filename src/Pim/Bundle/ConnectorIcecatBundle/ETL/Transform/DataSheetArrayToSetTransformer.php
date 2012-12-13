<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductAttributeToArrayTransformer;

use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductSetToArrayTransformer;

use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Pim\Bundle\CatalogBundle\Doctrine\ProductTemplateManager;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;
use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\TransformInterface;

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
     * @var \Pim\Bundle\CatalogBundle\Doctrine\ProductTemplateManager
     */
    protected $productTemplateManager;

    /**
     * Product data sheet to transform
     * @var IcecatProductDataSheet
     */
    protected $datasheet;

    /**
     * @var integer
     */
    protected $localeIcecat;

    /**
     * Constructor
     *
     * @param ProductManager         $productManager    product manager
     * @param ProductTemplateManager $productTplManager product template manager
     * @param IcecatProductDataSheet $datasheet         product datasheet
     */
    public function __construct(ProductManager $productManager, ProductTemplateManager $productTplManager,
        IcecatProductDataSheet $datasheet)
    {
        $this->productManager = $productManager;
        $this->productTemplateManager = $productTplManager;
        $this->datasheet = $datasheet;

        $this->localeIcecat = 1; // en_US
    }

    /**
     * {@inheritdoc}
     */
    public function transform()
    {
        // get datas
        $allData = json_decode($this->datasheet->getData(), true);
        $categoryData    = $allData['category'];
        $catFeatureData  = $allData['categoryfeaturegroups'];
        $prodFeatureData = $allData['productfeatures'];

        $groups = $this->transformToGroups($catFeatureData);

        $groups = $this->addAttributesToGroup($prodFeatureData, $groups);

        // prepare category data
        $setData = array(
            'id'     => null,
            'code'   => self::PREFIX .'-'. $categoryData['id'],
            'title'  => $categoryData['name'][$this->localeIcecat],
            'groups' => $groups
        );

        // add attributes
//         foreach ($prodFeatureData as $icecatAttId => $attribute) {
//             // get attribute
//             $attCode = self::PREFIX .'-'. $icecatAttId;
//             $att = $this->productManager->getAttributeRepository()->findOneByCode($attCode);

//             // add attribute to group
//             $groupCode = self::PREFIX .'-'. $attribute['CategoryFeatureGroup_ID'];
//             $setData['groups'][$groupCode]['attributes'][] = $att->getId();
//         }

        // initialize data transformer and transform data to set entity
        $dataTransformer = new ProductSetToArrayTransformer($this->productManager, $this->productTemplateManager);
        $set = $dataTransformer->reverseTransform($setData);

        return $set;
    }

    protected function transformToGroups($catFeatureData)
    {
        $groups = array();

        foreach ($catFeatureData as $icecatGroupId => $feature) {
            $groupCode = self::PREFIX .'-'. $icecatGroupId;

            $groupData = array(
                'id'    => null,
                'code'  => $groupCode,
                'title' => $feature[$this->localeIcecat],
                'attributes' => array()
            );

            $groups[$groupCode] = $groupData;
        }

        return $groups;
    }

    protected function addAttributesToGroup($prodFeatureData, $groups)
    {
        $dataTransformer = new ProductAttributeToArrayTransformer($this->productManager);

        // add attributes
        foreach ($prodFeatureData as $icecatAttId => $attribute) {
            // get attribute
            $attCode = self::PREFIX .'-'. $icecatAttId;
            $att = $this->productManager->getAttributeRepository()->findOneByCode($attCode);
            $attId = ($att) ? $att->getId() : null;

            // prepare array for transformer
            $attData = array(
                'id'    => $attId,
                'code'  => $attCode,
                'title' => $attribute['Name'][$this->localeIcecat]
            );

            // add attribute to group
            $groupCode = self::PREFIX .'-'. $attribute['CategoryFeatureGroup_ID'];
            $groups[$groupCode]['attributes'][] = $dataTransformer->reverseTransform($attData);
        }

        return $groups;
    }
}
