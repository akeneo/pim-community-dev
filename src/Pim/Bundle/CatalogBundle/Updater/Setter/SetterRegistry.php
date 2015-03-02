<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetterRegistry implements SetterRegistryInterface
{
    /** @var AttributeSetterInterface[] priorized attribute setters */
    protected $attributeSetters = [];

    /** @var FieldSorterInterface[] priorized field setters */
    protected $fieldSetters = [];

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function register(SetterInterface $setter)
    {
        if ($setter instanceof FieldSetterInterface) {
            $this->fieldSetters[] = $setter;
        }
        if ($setter instanceof AttributeSetterInterface) {
            $this->attributeSetters[] = $setter;
        }

        //TODO add an exception if the setter is neither FieldSetterInterface or AttributeSetterInterface
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5, use getAttributeSetter
     */
    public function get(AttributeInterface $attribute)
    {

        foreach ($this->attributeSetters as $setter) {
            if ($setter->supports($attribute)) {
                return $setter;
            }
        }

        throw new \LogicException(sprintf('Attribute "%s" is not supported by any setter', $attribute->getCode()));
    }

    /**
     * {@inheritdoc}
     */
    public function getSetter($code)
    {
        $attribute = $this->attributeRepository->findOneBy(['code' => $code]);

        if (null !== $attribute) {
            return $this->getAttributeSetter($attribute);
        }

        $setter = $this->getFieldSetter($code);
        if ($setter) {
            return $setter;
        }

        throw new \LogicException(sprintf('Field "%s" is not supported by any setter', $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldSetter($field)
    {
        foreach ($this->fieldFilters as $setter) {
            if ($setter->supportsField($field)) {
                return $setter;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSetter(AttributeInterface $attribute)
    {
        foreach ($this->attributeSetters as $setter) {
            if ($setter->supportsAttribute($attribute)) {
                return $setter;
            }
        }

        return null;
    }
}
