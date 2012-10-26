<?php
namespace Strixos\IcecatConnectorBundle\Transform;

use Akeneo\CatalogBundle\Model\BaseFieldFactory;

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
     * Get product type service
     * @var Service
     */
    protected $typeService;

    /**
    * Get product service
    * @var Service
    */
    protected $productService;
    
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
    public function __construct(EntityTypeManager $serviceType, EntityManager $serviceProduct, $prodData, $prodFeat, $localeCode)
    {
        $this->typeService = $serviceType;
        $this->productService = $serviceProduct;
        $this->prodData = $prodData;
        $this->prodFeat = $prodFeat;
        $this->localeCode = $localeCode;
    }

    /**
     * (non-PHPdoc)
     * @see Strixos\IcecatConnectorBundle\Transform.LanguagesTransform::transform()
     */
    public function transform()
    {
    	// TODO : directly use $this->var instead of copy var
        $prodData = $this->prodData;
        $prodFeat = $this->prodFeat;
        $localeCode = $this->localeCode;

        // 1) if not exists, create a new type
        $typeCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'];
        $return = $this->typeService->find($typeCode);
        if (!$return) {
            $this->typeService->create($typeCode);
        }

        // add all fields of prodData as general fields
        $productFieldCodeToValues = array();
        $generalGroupCode = 'General';
        $productSourceId = null;
        foreach ($prodData as $field => $value) {
            if ($field == 'id') {
                $fieldCode = self::PREFIX.'_source_id';
                $productSourceId = $value;
            } else {
                $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$prodData['CategoryId'].'-'.strtolower($field);
            }
            $this->typeService->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $generalGroupCode, $field);
            $productFieldCodeToValues[$fieldCode]= $value;
        }

        // create custom group for each features category
        foreach ($prodFeat as $featId => $featData) {
            foreach ($featData as $featName => $fieldData) {
                $groupCode = $featId.'-'.strtolower(str_replace(' ', '', $featName));
                foreach ($fieldData as $fieldId => $fieldData) {
                    $fieldName = $fieldData['name'];
                    $value = $fieldData['value'];
                    $fieldCode = self::PREFIX.'-'.$prodData['vendorId'].'-'.$featId.'-'.$fieldId;
                    $this->typeService->addField($fieldCode, BaseFieldFactory::FIELD_STRING, $groupCode, $fieldName);
                    $productFieldCodeToValues[$fieldCode]= $value;
                }
            }
        }

        // save type
        $this->typeService->persist();
        $this->typeService->flush();

        // 2) if not exists create a product
        $product = $this->productService->findBySourceId(self::PREFIX, $productSourceId);
        if (!$product) {
            $product = $this->typeService->newProductInstance();
        }
        $product->switchLocale($localeCode);

        // set product values
        foreach ($productFieldCodeToValues as $fieldCode => $value) {
            $product->setValue($fieldCode, $value);
        }

        // save
        $product->persist();
        $product->flush();
    }
}
