<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;

/**
 * Convert a basic representation of a variant group into a complex one bindable on a product form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductVariantGroupConverter
{
    /**
     * @var string
     */
    const VARIANT_GROUP_KEY = '[variant_group]';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function convert($data)
    {
        if (null !== $id = $this->getVariantGroupId($data)) {
            return array('variant_group' => $id);
        }

        return array();
    }

    /**
     * Get a variant group id
     *
     * @param array $data The submitted data
     *
     * @return int|null null if the self::VARIANT_GROUP_KEY wasn't sent in the data or the variant group
     * code doesn't exist
     */
    private function getVariantGroupId(array $data)
    {
        if (!array_key_exists(self::VARIANT_GROUP_KEY, $data)) {
            // TODO Warn that the variant group could not be determined
            return null;
        }
        if ($group = $this->getVariantGroup($data[self::VARIANT_GROUP_KEY])) {
            return $group->getId();
        }

        // TODO Warn that the variant group code does not exist
    }

    /**
     * Get a variant group by code
     *
     * @param string $code
     *
     * @return VariantGroup|null
     */
    private function getVariantGroup($code)
    {
        return $this->entityManager
            ->getRepository('PimCatalogBundle:VariantGroup')
            ->findOneBy(array('code' => $code));
    }
}
