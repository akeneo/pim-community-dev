<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeGroupRepository;

/**
 * Attribute group manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupManager
{
    /**
     * @var AttributeGroupRepository $repository
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param AttributeGroupRepository $repository
     */
    public function __construct(AttributeGroupRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the attribute group choices
     *
     * @return array
     */
    public function getAttributeGroupChoices()
    {
        $groups = $this->repository->findAllWithTranslations();
        $choices = array();
        foreach ($groups as $group) {
            $choices[$group->getCode()] = $group->getLabel();
        }
        asort($choices);

        return $choices;
    }
}
