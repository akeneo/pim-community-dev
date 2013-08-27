<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;

/**
 * Convert a basic representation of a value into a complex one bindable on a product form
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueConverter
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * array(
     *     'sku'        => 'sku-001',
     *     'name-en_US' => 'car',
     *     'name-fr_FR' => 'voiture,
     * )

     * array(
     *     sku' => array(
     *         'varchar' => 'sku-001',
     *     ),
     *     'name_en_US' => array(
     *         'varchar' => 'car'
     *     ),
     *     'name_fr_FR' => array(
     *         'varchar' => 'voiture'
     *     ),
     * )
     */
    public function convert($data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            $attribute = $this->getAttribute($key);
            $result[$key][$attribute->getBackendType()] = $value;
        }

        return $result;
    }

    private function getAttribute($code)
    {
        return $this->entityManager
            ->getRepository('PimProductBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }
}
