<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;

/**
 * Updates and validates a variant group
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        GroupTypeRepositoryInterface $groupTypeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->groupTypeRepository = $groupTypeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     "code": "mycode",
     *     "labels": {
     *         "en_US": "T-shirt very beautiful",
     *         "fr_FR": "T-shirt super beau"
     *     }
     *     "axis": ["main_color", "secondary_color"],
     *     "type": "VARIANT"
     * }
     */
    public function update($variantGroup, array $data, array $options = [])
    {
        if (!$variantGroup instanceof GroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "%s" provided.',
                    ClassUtils::getClass($variantGroup)
                )
            );
        }

        foreach ($data as $field => $data) {
            $this->setData($variantGroup, $field, $data);
        }

        return $this;
    }

    /**
     * @param GroupInterface $variantGroup
     * @param string         $field
     * @param mixed          $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(GroupInterface $variantGroup, $field, $data)
    {
        switch ($field) {
            case 'code':
                $this->setCode($variantGroup, $data);
                break;

            case 'type':
                $this->setType($variantGroup, $data);
                break;

            case 'labels':
                $this->setLabels($variantGroup, $data);
                break;

            case 'axis':
                $this->setAxes($variantGroup, $data);
                break;
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param string         $code
     */
    protected function setCode(GroupInterface $variantGroup, $code)
    {
        $variantGroup->setCode($code);
    }

    /**
     * @param GroupInterface $variantGroup
     * @param string         $type
     *
     * @throws \InvalidArgumentException
     */
    protected function setType(GroupInterface $variantGroup, $type)
    {
        $type = $this->groupTypeRepository->findOneByIdentifier($type);
        if (null !== $type) {
            $variantGroup->setType($type);
        } else {
            throw new \InvalidArgumentException(sprintf('Type "%s" does not exist', $type));
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $labels
     *
     * @throws \InvalidArgumentException
     */
    protected function setLabels(GroupInterface $variantGroup, array $labels)
    {
        foreach ($labels as $localeCode => $label) {
            $variantGroup->setLocale($localeCode);
            $translation = $variantGroup->getTranslation();
            $translation->setLabel($label);
        }
    }

    /**
     * @param GroupInterface $variantGroup
     * @param array          $axes
     *
     * @throws \InvalidArgumentException
     */
    protected function setAxes(GroupInterface $variantGroup, array $axes)
    {
        if (null !== $variantGroup->getId()) {
            if (array_diff($this->getOriginalAxes($variantGroup->getAxisAttributes()), array_values($axes))) {
                throw new \InvalidArgumentException('Attributes: This property cannot be changed.');
            }
        }

        foreach ($axes as $axis) {
            $attribute = $this->attributeRepository->findOneByIdentifier($axis);
            if (null !== $attribute) {
                $variantGroup->addAxisAttribute($attribute);
            } else {
                throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $axis));
            }
        }
    }

    /**
     * @param Collection $axes
     *
     * @return array
     */
    protected function getOriginalAxes(Collection $axes)
    {
        $data = [];
        foreach ($axes as $axis) {
            $data[] = $axis->getCode();
        }

        return $data;
    }
}
