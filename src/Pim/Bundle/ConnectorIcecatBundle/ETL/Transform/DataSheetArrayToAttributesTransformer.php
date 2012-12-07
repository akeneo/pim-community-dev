<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

use Pim\Bundle\CatalogBundle\Form\DataTransformer\ProductAttributeToArrayTransformer;

use Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces\TransformInterface;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;
use Pim\Bundle\ConnectorIcecatBundle\Document\IcecatProductDataSheet;

/**
 * Aims to transform product data sheet data to attribute instances
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DataSheetArrayToAttributesTransformer implements TransformInterface
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
     * Product data sheet to transform
     * @var IcecatProductDataSheet
     */
    protected $datasheet;

    /**
     * Constructor
     *
     * @param ProductManager         $productManager product manager
     * @param IcecatProductDataSheet $datasheet      product datasheet
     */
    public function __construct(\Pim\Bundle\CatalogBundle\Doctrine\ProductManager $productManager, IcecatProductDataSheet $datasheet)
    {
        $this->productManager = $productManager;
        $this->datasheet = $datasheet;
    }

    /**
     * {@ineritedDoc}
     *
     * @return
     */
    public function transform()
    {
        $attributes = array();
        $localeIcecat = 1; // en_US

        // get datas
        $allData = json_decode($this->datasheet->getData(), true);

        $prodFeatureData = $allData['productfeatures'];
        var_dump($allData); exit();

        // initialize data transformer
        $dataTransformer = new ProductAttributeToArrayTransformer($this->productManager);

        // Transform product features datas to attributes
        foreach ($prodFeatureData as $icecatAttId => $attribute) {
            // prepare array for transformer
            $attData = array(
                'id'    => null,
                'code'  => self::PREFIX .'-'. $icecatAttId,
                'title' => $attribute['Name'][$localeIcecat]
            );

            // transform data to attribute entity
            $attribute = $dataTransformer->reverseTransform($attData);
            $attributes[] = $attribute;

            // persist object
//             $this->productManager->getPersistenceManager()->persist($attribute);
        }

        return $attributes;
    }
}
