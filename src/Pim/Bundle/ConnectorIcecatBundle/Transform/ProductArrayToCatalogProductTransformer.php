<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Transform;

use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

/**
 * Aims to transform array product data to catalog product instance
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductArrayToCatalogProductTransformer implements TransformInterface
{
    /**
     * @staticvar string
     */
    const PREFIX = 'icecat';

    /**
     * Get product manager service
     * @var Service
     */
    protected $productManager;

    /**
     * @var array
     */
    protected $prodData;

    /**
     * @var array
     */
    protected $prodFeat;

    /**
     * @var string
     */
    protected $localeCode;

    /**
     * Constructor
     *
     * @param ProductManager $productManager
     * @param array          $prodData
     * @param array          $prodFeat
     * @param string         $localeCode
     */
    public function __construct(\Pim\Bundle\CatalogBundle\Doctrine\ProductManager $productManager, $prodData, $prodFeat, $localeCode)
    {
        $this->productManager = $productManager;
        $this->prodData = $prodData;
        $this->prodFeat = $prodFeat;
        $this->localeCode = $localeCode;
    }

    /**
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Transform.LanguagesTransform::transform()
     */
    public function transform()
    {
        // TODO : directly use $this->var instead of copy var
        $persistanceManager = $this->productManager->getPersistenceManager();
        $prodData = $this->prodData;
        $prodFeat = $this->prodFeat;
        $localeCode = $this->localeCode;

        // 1) if not exists, create a new type
        $setCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'];
        $setRepository = $this->productManager->getSetRepository();
        $set = $setRepository->findOneByCode($setCode);
        if (!$set) {
            $set = $this->productManager->getNewSetInstance();
            $set->setCode($setCode);
            $set->setTitle($setCode);
        }

        // 2) add all attributes of prodData as general attributes
        $ProductAttributeCodeToValues = array();
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
            $ProductAttributeCodeToValues[$attributeCode]= $valueData;

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

                    $ProductAttributeCodeToValues[$attributeCode]= $valueData;

                    // TODO : deal with existing values
                    $value = $this->productManager->getNewAttributeValueInstance();
                    $value->setAttribute($attribute);
                    $value->setData($valueData);
                    $productValues[]= $value;
                }
            }
        }

        // 4) save type

        $persistanceManager->persist($set);
        $persistanceManager->flush();

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
        // TODO $product->switchLocale($localeCode);

        // set product values
        foreach ($productValues as $value) {
            $product->addValue($value);
        }

        // save
        $persistanceManager->persist($product);
        $persistanceManager->flush();
    }
}
