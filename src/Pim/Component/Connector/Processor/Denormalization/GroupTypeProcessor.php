<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Factory\GroupTypeFactory;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
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
    /** @var ArrayConverterInterface */
    protected $groupTypeConverter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GroupTypeFactory */
    protected $groupTypeFactory;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ArrayConverterInterface               $groupTypeConverter
     * @param GroupTypeFactory                      $groupTypeFactory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $groupTypeConverter,
        GroupTypeFactory $groupTypeFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->groupTypeConverter = $groupTypeConverter;
        $this->groupTypeFactory   = $groupTypeFactory;
        $this->updater            = $updater;
        $this->validator          = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->groupTypeConverter->convert($item);
        $groupType = $this->findOrCreateGroupType($convertedItem);

        try {
            $this->updater->update($groupType, $convertedItem);
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
     * @param array $convertedItem
     *
     * @return GroupTypeInterface
     */
    protected function findOrCreateGroupType(array $convertedItem)
    {
        $groupType = $this->findObject($this->repository, $convertedItem);
        if (null === $groupType) {
            return $this->groupTypeFactory->create();
        }

        return $groupType;
    }
}
