<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Repository\GroupTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Group manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManager
{
    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
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
     * Get axis as choice list
     *
     * @deprecated not used anymore except in datagrid configuration, will be removed in 1.5
     *
     * @return array
     */
    public function getAvailableAxisChoices()
    {
        $attributes = $this->attributeRepository->findAllAxis();

        $choices = [];
        foreach ($attributes as $attribute) {
            $choices[$attribute->getId()] = $attribute->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * Get axis as choice list
     *
     * @param bool $isVariant
     *
     * @deprecated not used anymore except in controller which should use the repo, will be removed in 1.5
     *
     * @return array
     */
    public function getTypeChoices($isVariant)
    {
        $types = $this->groupTypeRepository->findBy(['variant' => $isVariant]);

        $choices = [];
        foreach ($types as $type) {
            $choices[$type->getId()] = $type->getLabel();
        }
        asort($choices);

        return $choices;
    }
}
