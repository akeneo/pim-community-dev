<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;

/**
 * Creates and configures a group instance.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFactory implements SimpleFactoryInterface
{
    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var string */
    protected $groupClass;

    /**
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     * @param string                       $groupClass
     */
    public function __construct(
        GroupTypeRepositoryInterface $groupTypeRepository,
        $groupClass
    ) {
        $this->groupClass = $groupClass;
        $this->groupTypeRepository = $groupTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->groupClass();
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
        $group = $this->create();

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
