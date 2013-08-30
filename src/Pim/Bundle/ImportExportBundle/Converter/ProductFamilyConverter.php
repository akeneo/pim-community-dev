<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;

/**
 * Convert a basic representation of a family into a complex one bindable on a product form
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFamilyConverter
{
    const FAMILY_KEY = '[family]';

    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function convert($data)
    {
        if (null !== $id = $this->getFamilyId($data)) {
            return array('family' => $id);
        }

        return array();
    }

    /**
     * Get a family id
     *
     * @param array $data The submitted data
     *
     * @return int|null null if the self::FAMILY_KEY wasn't sent in the data or the family code doesn't exist
     */
    private function getFamilyId(array $data)
    {
        if (!array_key_exists(self::FAMILY_KEY, $data)) {
            // TODO Warn that the family could not be determined
            return null;
        }
        if ($family = $this->getFamily($data[self::FAMILY_KEY])) {
            return $family->getId();
        }

        // TODO Warn that the family code does not exist
    }

    /**
     * Get a family by code
     *
     * @param string $code
     *
     * @return Family|null
     */
    private function getFamily($code)
    {
        return $this->entityManager
            ->getRepository('PimProductBundle:Family')
            ->findOneBy(array('code' => $code));
    }
}
