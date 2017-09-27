<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

/**
 * Creates and configures a group instance.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFactory implements SimpleFactoryInterface
{
    /** @var string */
    protected $metricClass;

    /** @var SimpleFactoryInterface */
    protected $productTemplateFactory;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /**
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     * @param SimpleFactoryInterface       $productTemplateFactory
     * @param string                       $groupClass
     */
    public function __construct(
        GroupTypeRepositoryInterface $groupTypeRepository,
        SimpleFactoryInterface $productTemplateFactory,
        $groupClass
    ) {
        $this->groupClass = $groupClass;
        $this->groupTypeRepository = $groupTypeRepository;
        $this->productTemplateFactory = $productTemplateFactory;
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
