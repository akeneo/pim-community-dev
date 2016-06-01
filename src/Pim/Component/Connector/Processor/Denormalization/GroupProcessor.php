<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\GroupFactory;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
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
    /** @var ArrayConverterInterface */
    protected $groupConverter;

    /** @var ObjectUpdaterInterface */
    protected $groupUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GroupFactory */
    protected $groupFactory;

    /**
     * @param ArrayConverterInterface               $groupConverter
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param GroupFactory                          $groupFactory
     * @param ObjectUpdaterInterface                $groupUpdater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        ArrayConverterInterface $groupConverter,
        IdentifiableObjectRepositoryInterface $repository,
        GroupFactory $groupFactory,
        ObjectUpdaterInterface $groupUpdater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->groupConverter = $groupConverter;
        $this->groupFactory   = $groupFactory;
        $this->groupUpdater   = $groupUpdater;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->convertItemData($item);
        $group = $this->findOrCreateGroup($convertedItem);

        try {
            $this->updateGroup($group, $convertedItem);
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
     * @param array $item
     *
     * @return array
     */
    protected function convertItemData(array $item)
    {
        return $this->groupConverter->convert($item);
    }

    /**
     * Find or create group
     *
     * @param array $convertedItem
     *
     * @return GroupInterface
     */
    protected function findOrCreateGroup(array $convertedItem)
    {
        if (null === $group = $this->findObject($this->repository, $convertedItem)) {
            $group = $this->groupFactory->createGroup($convertedItem['type']);
        }

        $isExistingGroup = (null !== $group->getType() && true === $group->getType()->isVariant());
        if ($isExistingGroup) {
            $this->skipItemWithMessage(
                $convertedItem,
                sprintf('Cannot process variant group "%s", only groups are accepted', $convertedItem['code'])
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
