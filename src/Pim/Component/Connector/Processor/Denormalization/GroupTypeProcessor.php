<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\GroupTypeFactory;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Group type import processor
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeProcessor extends AbstractProcessor
{
    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GroupTypeFactory */
    protected $groupTypeFactory;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param GroupTypeFactory                      $groupTypeFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        GroupTypeFactory $groupTypeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->groupTypeFactory   = $groupTypeFactory;
        $this->updater            = $updater;
        $this->validator          = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $groupType = $this->findOrCreateGroupType($item);

        try {
            $this->updater->update($groupType, $item);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($groupType);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $groupType;
    }

    /**
     * @param array $item
     *
     * @return GroupTypeInterface
     */
    protected function findOrCreateGroupType(array $item)
    {
        $groupType = $this->findObject($this->repository, $item);
        if (null === $groupType) {
            return $this->groupTypeFactory->create();
        }

        return $groupType;
    }
}
