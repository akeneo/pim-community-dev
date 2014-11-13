<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

/**
 * Registry of setters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetterRegistry implements SetterRegistryInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var array */
    protected $setters;

    /**
     * @param AttributeRepository $repository
     */
    public function __construct(AttributeRepository $repository)
    {
        $this->attributeRepository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function register(SetterInterface $setter)
    {
        $this->setters[] = $setter;
    }

    /**
     * {@inheritdoc}
     */
    public function get($field)
    {
        $attribute = $this->attributeRepository->findOneByCode($field);
        // TODO : other possiblity is to have different support methods for attribute and field in setters
        // as in product query builder but i have the feeling that only the attribute type matters,
        // we'll not create many setters for one attribute type
        if ($attribute !== null) {
            $setter = $this->getAttributeSetter($attribute);
        } else {
            $setter = $this->getFieldSetter($field);
        }

        if ($setter === null) {
            throw new \LogicException(sprintf('%s is not supported by any setter', $field));
        }

        return $setter;

    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return SetterInterface
     */
    protected function getAttributeSetter(AttributeInterface $attribute)
    {
        foreach ($this->setters as $setter) {
            if ($setter->supports($attribute->getAttributeType())) {
                return $setter;
            }
        }

        return null;
    }

    /**
     * @param string field
     *
     * @return SetterInterface
     */
    protected function getFieldSetter($field)
    {
        foreach ($this->setters as $setter) {
            if ($setter->supports($field)) {
                return $setter;
            }
        }

        return null;
    }
}
