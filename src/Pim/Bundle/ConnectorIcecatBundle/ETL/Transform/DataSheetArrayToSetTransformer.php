<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Transform;

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
     * (non-PHPdoc)
     * @see Pim\Bundle\ConnectorIcecatBundle\Transform.LanguagesTransform::transform()
     */
    public function transform()
    {
        echo "\nDataSheet array to set transformer\n";
        $localeIcecat = 1; // en_US

        // TODO : directly use $this->var instead of copy var
        $persistanceManager = $this->productManager->getPersistenceManager();
        $allData = json_decode($this->datasheet->getData(), true);

        $catData = $allData['category'];
        $catFeatureData = $allData['categoryfeaturegroups'];

        // Transform features
        foreach ($prodFeatureData as $icecatId => $attribute) {
            $attCode = self::PREFIX .'-'. $icecatId;

            $att = $this->productManager->getAttributeRepository()->findOneByCode($attCode);
            if (!$att) {
                $att = $this->productManager->getNewAttributeInstance();
                $att->setCode($attCode);
                $att->setTitle($attribute['Name'][$localeIcecat]);

                // persists attribute
                $this->productManager->getPersistenceManager()->persist($att);
            }
        }
    }
}
