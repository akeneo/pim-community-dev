<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\GroupTranslation;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;

/**
 * Group flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupDenormalizer extends AbstractEntityDenormalizer
{
    /** @var ReferableEntityRepositoryInterface */
    protected $groupTypeRepository;

    /** @var ReferableEntityRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ManagerRegistry                    $managerRegistry
     * @param string                             $entityClass
     * @param ReferableEntityRepositoryInterface $groupTypeRepository
     * @param ReferableEntityRepositoryInterface $attributeRepository
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $entityClass,
        ReferableEntityRepositoryInterface $groupTypeRepository,
        ReferableEntityRepositoryInterface $attributeRepository
    ) {
        parent::__construct($managerRegistry, $entityClass);
        $this->groupTypeRepository = $groupTypeRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        /** @var GroupInterface $group */
        $group = $this->getEntity($data, $context);
        $this->setCode($group, $data);
        $this->setGroupType($group, $data);
        $this->setAxis($group, $data);
        $this->setLabels($group, $data);

        return $group;
    }

    /**
     * @param GroupInterface $group
     * @param array          $data
     */
    protected function setCode(GroupInterface $group, $data)
    {
        if (isset($data['code'])) {
            $group->setCode($data['code']);
        }
    }

    /**
     * @param GroupInterface $group
     * @param array          $data
     */
    protected function setGroupType(GroupInterface $group, $data)
    {
        if (isset($data['type'])) {
            $typeCode = $data['type'];
            /** @var GroupType|null $type */
            $type = $this->groupTypeRepository->findByReference($typeCode);
            if (!$type) {
                throw new \LogicException(
                    sprintf('Group Type with identifier "%s" not found', $typeCode)
                );
            }
            $group->setType($type);
        }
    }

    /**
     * @param GroupInterface $group
     * @param array          $data
     */
    protected function setAxis(GroupInterface $group, $data)
    {
        if (isset($data['axis']) && !empty($data['axis']) && $group->getId() === null) {
            $axisCodes = explode(',', $data['axis']);
            $attributes = [];
            foreach ($axisCodes as $code) {
                $attribute = $this->attributeRepository->findByReference($code);
                if (!$attribute) {
                    throw new \LogicException(
                        sprintf('Attribute with identifier "%s" not found', $code)
                    );
                }
                $attributes[] = $attribute;
            }
            $group->setAxisAttributes($attributes);
        }
    }

    /**
     * @param GroupInterface $group
     * @param array          $data
     */
    protected function setLabels(GroupInterface $group, $data)
    {
        foreach ($data as $field => $value) {
            $isLabel = false !== strpos($field, 'label-', 0);
            if ($isLabel) {
                $labelTokens = explode('-', $field);
                $localeCode = $labelTokens[1];
                /** @var GroupTranslation $translation */
                $translation = $group->getTranslation($localeCode);
                $translation->setLabel($value);
                $group->addTranslation($translation);
            }
        }
    }
}
