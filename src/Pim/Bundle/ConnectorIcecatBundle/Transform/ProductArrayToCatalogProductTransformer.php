<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Transform;

use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

use Bap\Bundle\FlexibleEntityBundle\Doctrine\EntityManager;
use Bap\Bundle\FlexibleEntityBundle\Doctrine\EntityTypeManager;
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
     * @param EntityTypeManager $serviceType
     * @param EntityManager $serviceProduct
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
        $prodData = $this->prodData;
        $prodFeat = $this->prodFeat;
        $localeCode = $this->localeCode;

        // 1) if not exists, create a new type
        $typeCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'];
        $typeRepository = $this->productManager->getTypeRepository();
        $type = $typeRepository->findOneByCode($typeCode);
        if (!$type) {
            $classType = $this->productManager->getTypeClass();
            $type = new $classType();
            $type->setCode($typeCode);
            $type->setTitle($typeCode);
        }

        // 2) add all fields of prodData as general fields
        $productFieldCodeToValues = array();

        // 2a) create general group if not exists
        $generalGroupCode = 'General';
        $group = $type->getGroup($generalGroupCode);
        if (!$group) {
            $classGroup = $this->productManager->getGroupClass();
            $group = new $classGroup();
            $group->setCode($generalGroupCode);
            $group->setTitle($generalGroupCode);
            $type->addGroup($group);
        }

        // 2b) add fields
        foreach ($prodData as $field => $value) {

            if ($field == 'id') {
                $fieldCode = self::PREFIX.'_source_id';
                $productSourceId = $value;
            } else {
                $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'].'-'.strtolower($field);
            }

            // get field or create TODO: if it's already in other group ?
            $field = $group->getField($fieldCode);
            if (!$field) {
                $classField = $this->productManager->getFieldClass();
                $field = new $classField();
                $field->setCode($fieldCode);
                $field->setTitle($fieldCode);
                $field->setType(BaseFieldFactory::FIELD_STRING);
                // TODO unique etc ?
                $group->addField($field);
            }

            // prepare field code to value for next step
            $productFieldCodeToValues[$fieldCode]= $value;

        }

        // 3) create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {
            foreach ($featData as $featName => $fieldData) {
                $groupCode = 'feat-'.$featId;//.'-'.strtolower(str_replace('&', '', str_replace(' ', '', $featName)));

                foreach ($fieldData as $fieldId => $fieldData) {
                    $fieldName = $fieldData['name'];
                    $value = $fieldData['value'];
                    $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$featId.'-'.$fieldId;

                    // if not exists add group
                    $group = $type->getGroup($groupCode);
                    if (!$group) {
                        $classGroup = $this->productManager->getGroupClass();
                        $group = new $classGroup();
                        $group->setCode($groupCode);
                        $group->setTitle($groupCode);
                        $type->addGroup($group);
                    }

                    // get field or create TODO: if it's already in other group ?
                    $field = $group->getField($fieldCode);
                    if (!$field) {
                        $classField = $this->productManager->getFieldClass();
                        $field = new $classField();
                        $field->setCode($fieldCode);
                        $field->setTitle($fieldCode);
                        $field->setType(BaseFieldFactory::FIELD_STRING);
                        // TODO unique etc ?
                        $group->addField($field);
                    }

                    $productFieldCodeToValues[$fieldCode]= $value;
                }
            }
        }

        // 4) save type
        $persistanceManager = $this->productManager->getPersistenceManager();
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
        foreach ($productFieldCodeToValues as $fieldCode => $value) {
            $product->setValue($fieldCode, $value);
        }

        // save
        $persistanceManager->persist($product);
        $persistanceManager->flush();
    }
}
