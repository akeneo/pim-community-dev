<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\ArrayToObject\Flat;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\BaseConnectorBundle\Processor\ArrayToObject\AbstractProcessor;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Group import processor, allows to,
 *  - create / update groups (except variant group)
 *  - return the valid groups, throw exceptions to skip invalid ones
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProcessor extends AbstractProcessor
{
    /** @staticvar string */
    const CODE_FIELD = 'code';

    /** @staticvar string */
    const TYPE_FIELD = 'type';

    /** @staticvar string */
    const LABEL_FIELD = 'label';

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        /** @var GroupInterface $group */
        $group = $this->findOrCreateGroup($item);
        $this->updateGroup($group, $item);
        $this->validateGroup($group, $item);

        return $group;
    }

    /**
     * Find or create the group
     *
     * @param array $groupData
     *
     * @return GroupInterface
     */
    protected function findOrCreateGroup(array $groupData)
    {
        $group = $this->findOrCreateObject($this->repository, $groupData, $this->class);

        if (null === $group->getId() && $groupData[self::TYPE_FIELD] === 'VARIANT') {
            $this->skipItemWithMessage(
                $groupData,
                sprintf('Cannot process variant group "%s", only groups are accepted', $groupData[self::CODE_FIELD])
            );
        } elseif (null !== $group->getId() && $group->getType()->isVariant()) {
            $this->skipItemWithMessage(
                $groupData,
                sprintf('Group "%s" does not exist', $groupData[self::CODE_FIELD])
            );
        }

        return $group;
    }

    /**
     * Update the variant group fields
     *
     * @param GroupInterface $group
     * @param array          $groupData
     *
     * @return GroupInterface
     */
    protected function updateGroup(GroupInterface $group, array $groupData)
    {
        $group = $this->denormalizer->denormalize(
            $groupData,
            $this->class,
            'csv',
            ['entity' => $group]
        );

        return $group;
    }

    /**
     * @param GroupInterface $group
     * @param array          $item
     */
    protected function validateGroup(GroupInterface $group, array $item)
    {
        $violations = $this->validator->validate($group);
        $this->skipItemWithConstraintViolations($item, $violations);
    }
}
