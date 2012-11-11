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
     * @param array $prodData
     * @param array $prodFeat
     * @param string $localeCode
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
        $typeCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'];
        $typeRepository = $this->productManager->getSetRepository();
        $type = $typeRepository->findOneByCode($typeCode);
        if (!$type) {
            $type = $this->productManager->getNewSetInstance();
            $type->setCode($typeCode);
            $type->setTitle($typeCode);
        }

        // 2) add all fields of prodData as general fields
        $ProductAttributeCodeToValues = array();
        $productValues = array();

        // 2a) create general group if not exists
        $generalGroupCode = 'General';
        $group = $type->getGroup($generalGroupCode);
        if (!$group) {
            $group = $this->productManager->getNewGroupInstance();
            $group->setCode($generalGroupCode);
            $group->setTitle($generalGroupCode);
            $type->addGroup($group);
        }

        // 2b) add fields
        foreach ($prodData as $fieldName => $valueData) {

            if ($fieldName == 'id') {
                $fieldCode = self::PREFIX.'_source_id';
                $productSourceId = $valueData;
            } else {
                $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'].'-'.strtolower($fieldName);
            }

            // get field or create TODO: if it's already in other group ?
            $field = $group->getField($fieldCode);
            if (!$field) {
                $field = $this->productManager->getNewAttributeInstance();
                $field->setCode($fieldCode);
                $field->setTitle($fieldName);
                $field->setType(BaseFieldFactory::FIELD_STRING);
                $persistanceManager->persist($field);
                // TODO unique etc ?
                $group->addField($field);
            }

            // prepare field code to value for next step
            $ProductAttributeCodeToValues[$fieldCode]= $valueData;

            // TODO : deal with existing values
            $value = $this->productManager->getNewAttributeValueInstance();
            $value->setField($field);
            $value->setData($valueData);
            $productValues[]= $value;

        }

        // 3) create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {

            foreach ($featData as $featName => $fieldData) {

                $groupCode = 'feat-'.$featId;//.'-'.strtolower(str_replace('&', '', str_replace(' ', '', $featName)));

                foreach ($fieldData as $fieldId => $fieldData) {

                    $fieldName = $fieldData['name'];
                    $valueData = $fieldData['value'];
                    $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$featId.'-'.$fieldId;

                    // if not exists add group
                    $group = $type->getGroup($groupCode);
                    if (!$group) {
                        $classGroup = $this->productManager->getGroupClass();
                        $group = new $classGroup();
                        $group->setCode($groupCode);
                        $group->setTitle($featName);
                        $type->addGroup($group);
                    }

                    // get field or create TODO: if it's already in other group ?
                    $field = $group->getField($fieldCode);
                    if (!$field) {
                        $classField = $this->productManager->getAttributeClass();
                        $field = new $classField();
                        $field->setCode($fieldCode);
                        $field->setTitle($fieldName);
                        $field->setType(BaseFieldFactory::FIELD_STRING);
                        $persistanceManager->persist($field);
                        // TODO unique etc ?
                        $group->addField($field);
                    }

                    $ProductAttributeCodeToValues[$fieldCode]= $valueData;

                    // TODO : deal with existing values
                    $value = $this->productManager->getNewAttributeValueInstance();
                    $value->setField($field);
                    $value->setData($valueData);
                    $productValues[]= $value;
                }
            }
        }

        // 4) save type

        $persistanceManager->persist($type);
        $persistanceManager->flush();

        // 5) if not exists create a product
        $productSourceId = null;
        $productRepository = $this->productManager->getEntityRepository();
//        $product = $productRepository->findBySourceId(self::PREFIX, $productSourceId);

        $sourceField = self::PREFIX.'_source_id';
        $product = null; //$productRepository->findOneBy(array($sourceField => $productSourceId));

        if (!$product) {
            $classProd = $this->productManager->getEntityClass();
            $product = new $classProd();
            $product->setType($type);
        }
 // TODO       $product->switchLocale($localeCode);

        // set product values
        foreach ($productValues as $value) {
            $product->addValue($value);
        }

        // save
        $persistanceManager->persist($product);
        $persistanceManager->flush();
    }
}
