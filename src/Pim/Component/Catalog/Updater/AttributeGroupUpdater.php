<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Manager\AttributeGroupManager;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeGroupRepositoryInterface;

/**
 * Updates an attribute group
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeGroupManager */
    protected $attributeGroupRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param AttributeGroupRepositoryInterface     $attributeGroupRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'code'       => 'sizes',
     *     'sort_order' => 1,
     *     'attributes' => ['size', 'main_color'],
     *     'label'      => [
     *         'en_US' => 'Sizes',
     *         'fr_FR' => 'Tailles'
     *     ]
     * ]
     */
    public function update($attributeGroup, array $data, array $options = [])
    {
        if (!$attributeGroup instanceof AttributeGroupInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($attributeGroup),
                'Pim\Component\Catalog\Model\AttributeGroupInterface'
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($attributeGroup, $field, $value);
        }

        return $this;
    }

    /**
     * @param AttributeGroupInterface $attributeGroup
     * @param string                  $field
     * @param mixed                   $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData($attributeGroup, $field, $data)
    {
        if ('code' == $field) {
            $attributeGroup->setCode($data);
        } elseif ('sort_order' == $field) {
            $attributeGroup->setSortOrder($data);
        } elseif ('attributes' == $field) {
            $this->setAttributes($attributeGroup, $data);
        } elseif ('label' == $field) {
            foreach ($data as $locale => $label) {
                $attributeGroup->setLocale($locale);
                $attributeGroup->setLabel($label);
            }
        }
    }

    /**
     * @param $attributeCode
     *
     * @return AttributeInterface|null
     */
    protected function findAttribute($attributeCode)
    {
        return $this->attributeRepository->findOneByIdentifier($attributeCode);
    }

    /**
     * @param AttributeGroupInterface $attributeGroup
     * @param string[]                $data
     *
     * @throws InvalidPropertyException
     */
    protected function setAttributes(AttributeGroupInterface $attributeGroup, array $data)
    {
        if ('other' === $attributeGroup->getCode()) {
            return;
        }

        $defaultGroup = $this->attributeGroupRepository->findDefaultAttributeGroup();

        foreach ($attributeGroup->getAttributes() as $attribute) {
            if (!in_array($attribute->getCode(), $data)) {
                $defaultGroup->addAttribute($attribute);
            }
        }

        foreach ($data as $attributeCode) {
            $attribute = $this->findAttribute($attributeCode);
            if (null === $attribute) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    'attributes',
                    'attribute code',
                    'The attribute does not exist',
                    'updater',
                    'attribute group',
                    $attributeCode
                );
            }
            $attributeGroup->addAttribute($attribute);
        }
    }
}
