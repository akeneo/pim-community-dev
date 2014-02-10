<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Family manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyManager
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Get choices
     *
     * @return array
     */
    public function getChoices()
    {
        $choices = $this->getRepository()->getChoices();
        asort($choices);

        return $choices;
    }

    /**
     * Returns the entity repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->doctrine->getRepository('PimCatalogBundle:Family');
    }
}
