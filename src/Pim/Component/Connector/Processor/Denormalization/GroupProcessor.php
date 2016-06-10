<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Model\GroupInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Group import processor, allows to,
 *  - create / update groups
 *  - return the valid group, throw exceptions to skip invalid ones
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProcessor extends AbstractProcessor
{
    /** @var ObjectUpdaterInterface */
    protected $groupUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GroupFactory */
    protected $groupFactory;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param GroupFactory                          $groupFactory
     * @param ObjectUpdaterInterface                $groupUpdater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        GroupFactory $groupFactory,
        ObjectUpdaterInterface $groupUpdater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->groupFactory   = $groupFactory;
        $this->groupUpdater   = $groupUpdater;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $group = $this->findOrCreateGroup($item);

        try {
            $this->updateGroup($group, $item);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validateGroup($group);
        if ($violations->count() > 0) {
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        return $group;
    }

    /**
     * Find or create group
     *
     * @param array $item
     *
     * @return GroupInterface
     */
    protected function findOrCreateGroup(array $item)
    {
        if (null === $group = $this->findObject($this->repository, $item)) {
            $group = $this->groupFactory->createGroup($item['type']);
        }

        $isExistingGroup = (null !== $group->getType() && true === $group->getType()->isVariant());
        if ($isExistingGroup) {
            $this->skipItemWithMessage(
                $item,
                sprintf('Cannot process variant group "%s", only groups are accepted', $item['code'])
            );
        }

        return $group;
    }

    /**
     * Update the group group fields
     *
     * @param GroupInterface $group
     * @param array          $convertedItem
     */
    protected function updateGroup(GroupInterface $group, array $convertedItem)
    {
        $this->groupUpdater->update($group, $convertedItem);
    }

    /**
     * @param GroupInterface $group
     *
     * @throws InvalidItemException
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    protected function validateGroup(GroupInterface $group)
    {
        $violations = $this->validator->validate($group);

        return $violations;
    }
}
