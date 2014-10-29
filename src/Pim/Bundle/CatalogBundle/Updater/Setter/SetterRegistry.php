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
        $this->setters = [];
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
        if ($attribute === null) {
            throw new \LogicException(sprintf('Attribute "%s" not found', $field));
        }

        foreach ($this->setters as $setter) {
            if ($setter->supports($attribute->getAttributeType())) {
                return $setter;
            }
        }

        // TODO :
        // - updatable fields are : family, groups, categories, enabled, association
        // - not updatable are : id, created, updated
        // so the best shot could be to provided dedicated methods in ProductUpdater for the updatable fields
        // for instance a setFamily(

        throw new \LogicException(sprintf('Field "%s" is not supported by any setter', $field));
    }
}
