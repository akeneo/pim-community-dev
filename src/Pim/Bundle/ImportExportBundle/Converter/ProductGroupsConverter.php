<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Doctrine\ORM\EntityManager;

/**
 * Convert a basic representation of a groups into a complex one bindable on a product form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGroupsConverter
{
    /**
     * @var string
     */
    const GROUPS_KEY = '[groups]';

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
        if (null !== $ids = $this->getGroupIds($data)) {
            return array('groups' => $ids);
        }

        return array();
    }

    /**
     * Get group ids
     *
     * @param array $data The submitted data
     *
     * @return int|null null if the self::GROUPS_KEY wasn't sent in the data or the group
     * codes dont exist
     */
    private function getGroupIds(array $data)
    {
        if (!array_key_exists(self::GROUPS_KEY, $data)) {
            // TODO Warn that the groups could not be determined
            return null;
        }

        $ids = array();
        foreach (explode(',', $data[self::GROUPS_KEY]) as $code) {
            $group = $this->getGroup($code);
            if (!$group) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Couldn\'t find a group with code "%s"',
                        $code
                    )
                );
            }
            $ids[] = $group->getId();
        }

        return $ids;
    }

    /**
     * Get a group by code
     *
     * @param string $code
     *
     * @return Group|null
     */
    private function getGroup($code)
    {
        return $this->entityManager
            ->getRepository('PimCatalogBundle:Group')
            ->findOneBy(array('code' => $code));
    }
}
