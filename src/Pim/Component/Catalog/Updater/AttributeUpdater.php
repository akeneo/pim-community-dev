<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Updates an attribute.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeUpdater implements ObjectUpdaterInterface
{
    /** @var AttributeGroupRepositoryInterface */
    protected $attributeGroupRepository;

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var array */
    protected $referenceDataType;

    /**
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param array                             $referenceDataType
     * @param ConfigurationRegistryInterface    $registry
     */
    public function __construct(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        array $referenceDataType,
        ConfigurationRegistryInterface $registry = null
    ) {
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->accessor                 = PropertyAccess::createPropertyAccessor();
        $this->registry                 = $registry;
        $this->referenceDataType        = $referenceDataType;
    }

    /**
     * {@inheritdoc}
     */
    public function update($attribute, array $data, array $options = [])
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\AttributeInterface", "%s" provided.',
                    ClassUtils::getClass($attribute)
                )
            );
        }

        $this->checkIfReferenceDataExists($data);

        foreach ($data as $field => $value) {
            $this->setData($attribute, $field, $value);
        }

        return $this;
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $field
     * @param mixed              $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(AttributeInterface $attribute, $field, $data)
    {
        if ('labels' === $field) {
            foreach ($data as $localeCode => $label) {
                $attribute->setLocale($localeCode);
                $translation = $attribute->getTranslation();
                $translation->setLabel($label);
            }
        } elseif ('group' === $field) {
            $attributeGroup = $this->findAttributeGroup($data);
            if (null !== $attributeGroup) {
                $attribute->setGroup($attributeGroup);
            } else {
                throw new \InvalidArgumentException(sprintf('AttributeGroup "%s" does not exist', $data));
            }
        } else {
            $this->accessor->setValue($attribute, $field, $data);
        }
    }

    /**
     * @param string $code
     *
     * @return AttributeGroupInterface|null
     */
    protected function findAttributeGroup($code)
    {
        $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($code);

        return $attributeGroup;
    }

    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    protected function checkIfReferenceDataExists($value)
    {
        if (in_array($value['attributeType'], $this->referenceDataType)) {
            if (!$this->registry->has($value['reference_data_name'])) {
                $references = array_keys($this->registry->all());
                throw new \InvalidArgumentException(
                    sprintf(
                        'Reference data "%s" does not exist. Allowed values are: %s',
                        $value['reference_data_name'],
                        implode(', ', $references)
                    )
                );
            }
        }

        return null;
    }
}
