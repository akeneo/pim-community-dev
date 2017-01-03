<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

/**
 * Updates and validates a group
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupUpdater implements ObjectUpdaterInterface
{
    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;
    
    /**
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        GroupTypeRepositoryInterface $groupTypeRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->groupTypeRepository = $groupTypeRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'code'   => 'mycode',
     *     'labels' => [
     *         'en_US' => 'T-shirt very beautiful',
     *         'fr_FR' => 'T-shirt super beau'
     *     ],
     *     'axis'   => ['size', 'color']
     * ]
     */
    public function update($group, array $data, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\GroupInterface", "%s" provided.',
                    ClassUtils::getClass($group)
                )
            );
        }

        foreach ($data as $field => $item) {
            $this->setData($group, $field, $item);
        }

        return $this;
    }

    /**
     * @param GroupInterface $group
     * @param string         $field
     * @param mixed          $data
     */
    protected function setData(GroupInterface $group, $field, $data)
    {
        switch ($field) {
            case 'code':
                $this->setCode($group, $data);
                break;
            case 'type':
                $this->setType($group, $data);
                break;
            case 'labels':
                $this->setLabels($group, $data);
                break;
            case 'axis':
                $this->setAxis($group, $data);
                break;
        }
    }

    /**
     * @param GroupInterface $group
     * @param string         $code
     */
    protected function setCode(GroupInterface $group, $code)
    {
        $group->setCode($code);
    }

    /**
     * @param GroupInterface $group
     * @param string         $type
     *
     * @throws \InvalidArgumentException
     */
    protected function setType(GroupInterface $group, $type)
    {
        $groupType = $this->groupTypeRepository->findOneByIdentifier($type);

        if (null === $groupType) {
            throw new \InvalidArgumentException(sprintf('Type "%s" does not exist', $type));
        }

        if ($groupType->isVariant()) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot process variant group "%s", only groups are accepted',
                $group->getCode()
            ));
        }

        $group->setType($groupType);
    }

    /**
     * @param GroupInterface $group
     * @param array          $labels
     */
    protected function setLabels(GroupInterface $group, array $labels)
    {
        foreach ($labels as $localeCode => $label) {
            $group->setLocale($localeCode);
            $translation = $group->getTranslation();
            $translation->setLabel($label);
        }
    }

    /**
     * @param GroupInterface $group
     * @param string[]       $attributeCodes
     *
     * @throws \InvalidArgumentException
     */
    protected function setAxis(GroupInterface $group, array $attributeCodes)
    {
        $attributes = [];
        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null === $attribute) {
                throw new \InvalidArgumentException(sprintf('Attribute "%s" does not exist', $attributeCode));
            }
            $attributes[] = $attribute;
        }
        $group->setAxisAttributes($attributes);
    }
}
