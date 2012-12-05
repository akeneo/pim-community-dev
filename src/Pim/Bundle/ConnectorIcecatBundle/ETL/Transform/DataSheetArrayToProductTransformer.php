<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

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
class DataSheetArrayToProductTransformer implements TransformInterface
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
     * Product data sheet to transform
     * @var ProductDataSheet
     */
    protected $datasheet;

    /**
     * Constructor
     *
     * @param ProductManager   $productManager product manager
     * @param ProductDataSheet $datasheet      product datasheet
     */
    public function __construct(\Pim\Bundle\CatalogBundle\Doctrine\ProductManager $productManager, IcecatProductDataSheet $datasheet)
    {
        $this->productManager = $productManager;
        $this->datasheet = $datasheet;
    }

    /**
     * {@inheritdoc}
     */
    public function transform()
    {
        $localeIcecat = 1; // en_us

        // TODO : directly use $this->var instead of copy var
        $persistanceManager = $this->productManager->getPersistenceManager();
        $allData = json_decode($this->datasheet->getData(), true);

        $prodData = $allData['basedata'];
        $catData = $allData['category'];
        $catFeatureData = $allData['categoryfeaturegroups'];
        $prodFeatureData = $allData['productfeatures'];

        // 1) if not exists, create a new type
        $setCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$catData['id'];
        $setRepository = $this->productManager->getSetRepository();
        $set = $setRepository->findOneByCode($setCode);
        if (!$set) {
            $set = $this->productManager->getNewSetInstance();
            $set->setCode($setCode);
            $set->setTitle($catData['name'][$localeIcecat]);
        }

        // 2) create group if not exists
        foreach ($catFeatureData as $catFeatureId => $catFeature) {
            $group = $set->getGroup($catFeatureId);
            if (!$group) {
                $group = $this->productManager->getNewGroupInstance();
                $group->setCode($catFeatureId);
                $group->setTitle($catFeature[$localeIcecat]);
                $set->addGroup($group);
            }
        }

        // 3) add attributes if not exists
        foreach ($prodFeatureData as $prodFeatId => $prodFeatData) {
                // get group
                $group = $set->getGroup($prodFeatData['CategoryFeatureGroup_ID']);

                // get attribute or create
                //TODO : update !
             /*   $attribute = $group->getAttribute($prodFeatId);
                if (!$attribute) {*/
                    $classField = $this->productManager->getAttributeClass();
                    $attribute = new $classField();
                    $attribute->setCode($prodFeatId);
                    $attribute->setTitle($prodFeatData['Name'][$localeIcecat]);
                    $attribute->setType(BaseFieldFactory::FIELD_STRING);
                    $persistanceManager->persist($attribute);
                    $group->addAttribute($attribute);
               // }
        }

        // 4) get / create product
//        $product = $this->productManager->getproductRepository()->findOneByCode($setCode);
// TODO update

return $set;


        // TODO use pivotal transformer
/*
        $transformer = new ProductToArrayTransformer($this->getProductManager());
        $instance = $transformer->reverseTransform($productData);
*/

/*
        // 2) add all attributes of prodData as general attributes
        $productAttributeCodeToValues = array();
        $productValues = array();

        // 2a) create general group if not exists
        $generalGroupCode = 'General';
        $group = $set->getGroup($generalGroupCode);
        if (!$group) {
            $group = $this->productManager->getNewGroupInstance();
            $group->setCode($generalGroupCode);
            $group->setTitle($generalGroupCode);
            $set->addGroup($group);
        }

        // 2b) add attributes
        foreach ($prodData as $attributeName => $valueData) {

            if ($attributeName == 'id') {
                $attributeCode = self::PREFIX.'_source_id';
                $productSourceId = $valueData;
            } else {
                $attributeCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'].'-'.strtolower($attributeName);
            }

            // get attribute or create TODO: if it's already in other group ?
            $attribute = $group->getAttribute($attributeCode);
            if (!$attribute) {
                $attribute = $this->productManager->getNewAttributeInstance();
                $attribute->setCode($attributeCode);
                $attribute->setTitle($attributeName);
                $attribute->setType(BaseFieldFactory::FIELD_STRING);
                $persistanceManager->persist($attribute);
                // TODO unique etc ?
                $group->addAttribute($attribute);
            }

            // prepare attribute code to value for next step
            $productAttributeCodeToValues[$attributeCode]= $valueData;

            // TODO : deal with existing values
            $value = $this->productManager->getNewAttributeValueInstance();
            $value->setAttribute($attribute);
            $value->setData($valueData);
            $productValues[]= $value;

        }

        // 3) create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {

            foreach ($featData as $featName => $attributeData) {

                $groupCode = 'feat-'.$featId;//.'-'.strtolower(str_replace('&', '', str_replace(' ', '', $featName)));

                foreach ($attributeData as $attributeId => $attributeData) {

                    $attributeName = $attributeData['name'];
                    $valueData = $attributeData['value'];
                    $attributeCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$featId.'-'.$attributeId;

                    // if not exists add group
                    $group = $set->getGroup($groupCode);
                    if (!$group) {
                        $classGroup = $this->productManager->getGroupClass();
                        $group = new $classGroup();
                        $group->setCode($groupCode);
                        $group->setTitle($featName);
                        $set->addGroup($group);
                    }

                    // get attribute or create TODO: if it's already in other group ?
                    $attribute = $group->getAttribute($attributeCode);
                    if (!$attribute) {
                        $classField = $this->productManager->getAttributeClass();
                        $attribute = new $classField();
                        $attribute->setCode($attributeCode);
                        $attribute->setTitle($attributeName);
                        $attribute->setType(BaseFieldFactory::FIELD_STRING);
                        $persistanceManager->persist($attribute);
                        // TODO unique etc ?
                        $group->addAttribute($attribute);
                    }

                    $productAttributeCodeToValues[$attributeCode]= $valueData;

                    // TODO : deal with existing values
                    $value = $this->productManager->getNewAttributeValueInstance();
                    $value->setAttribute($attribute);
                    $value->setData($valueData);
                    $productValues[]= $value;
                }
            }
        }
*/
        // 4) save set
/*        $persistanceManager->persist($set);
        $persistanceManager->flush();*/
/*
        // 5) if not exists create a product
        $productSourceId = null;
        $productRepository = $this->productManager->getEntityRepository();

        $sourceField = self::PREFIX.'_source_id';
        $product = null; //$productRepository->findOneBy(array($sourceField => $productSourceId));

        if (!$product) {
            $classProd = $this->productManager->getEntityClass();
            $product = new $classProd();
            $product->setSet($set);
        }

        // set product values
        foreach ($productValues as $value) {
            $product->addValue($value);
        }

        // save
        $persistanceManager->persist($product);
        $persistanceManager->flush();
        */
    }
}
