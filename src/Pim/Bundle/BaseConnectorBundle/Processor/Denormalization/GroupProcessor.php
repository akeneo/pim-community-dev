<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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

    /** @var DenormalizerInterface */
    protected $denormalizer;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /** @var string */
    protected $format;

    /** @var string */
    protected $class;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository   repository to search the object in
     * @param DenormalizerInterface                 $denormalizer denormalizer used to transform array to object
     * @param ValidatorInterface                    $validator    validator of the object
     * @param ObjectDetacherInterface               $detacher     detacher to remove it from UOW when skip
     * @param string                                $class        class of the object to instanciate in case if need
     * @param string                                $format       format use to denormalize
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        $class,
        $format
    ) {
        parent::__construct($repository, $validator);
        $this->denormalizer = $denormalizer;
        $this->detacher = $detacher;
        $this->format = $format;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        /** @var GroupInterface $group */
        $this->checkItemData($item);
        $group = $this->findOrCreateGroup($item);
        $this->updateGroup($group, $item);
        $this->validateGroup($group, $item);

        return $group;
    }

    /**
     * @param array $groupData
     */
    protected function checkItemData(array $groupData)
    {
        if (!isset($groupData[self::CODE_FIELD]) || empty($groupData[self::CODE_FIELD])) {
            $this->skipItemWithMessage($groupData, 'Code must be provided');
        }
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
        if (null === $group = $this->findObject($this->repository, $groupData)) {
            $group = new $this->class();
        }

        $isVariantGroup = false;
        if ((null === $group->getId() && $groupData[self::TYPE_FIELD] === 'VARIANT') ||
            (null !== $group->getId() && $group->getType()->isVariant())
        ) {
            $isVariantGroup = true;
        }

        if ($isVariantGroup) {
            $this->skipItemWithMessage(
                $groupData,
                sprintf('Cannot process variant group "%s", only groups are accepted', $groupData[self::CODE_FIELD])
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
            $this->format,
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
        if ($violations->count() !== 0) {
            $this->detachObject($group);
            $this->skipItemWithConstraintViolations($item, $violations);
        }
    }

    /**
     * Detaches the object from the unit of work
     *
     * Detach an object from the UOW is the responsibility of the writer, but to do so, it should know the
     * skipped items or we should use an explicit persist strategy
     *
     * @param mixed $object
     */
    protected function detachObject($object)
    {
        $this->detacher->detach($object);
    }
}
