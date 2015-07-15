<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;

/**
 * Group factory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFactory
{
    /** @var string */
    protected $metricClass;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /**
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     * @param string                       $groupClass
     */
    public function __construct(GroupTypeRepositoryInterface $groupTypeRepository, $groupClass)
    {
        $this->groupClass          = $groupClass;
        $this->groupTypeRepository = $groupTypeRepository;
    }

    /**
     * Create and configure a group instance
     *
     * @param string $groupTypeCode
     *
     * @return GroupInterface
     */
    public function createGroup($groupTypeCode = null)
    {
        $group = new $this->groupClass();

        if (null !== $groupTypeCode) {
            $groupType = $this->groupTypeRepository->findOneByIdentifier($groupTypeCode);
            if (null === $groupType) {
                throw new \InvalidArgumentException(sprintf('Group type with code "%s" was not found', $groupTypeCode));
            }
            $group->setType($groupType);
        }

        return $group;
    }
}
