<?php

namespace Oro\Bundle\OrganizationBundle\Twig;

use Oro\Bundle\OrganizationBundle\Entity\Manager\BusinessUnitManager;

class BusinessUnitExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_business_unit';

    /**
     * @var BusinessUnitManager
     */
    protected $manager;

    /**
     * @param BusinessUnitManager $manager
     */
    public function __construct(BusinessUnitManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_get_business_units_count' => new \Twig_Function_Method(
                $this,
                'getBusinessUnitCount'
            )
        );
    }

    public function getBusinessUnitCount()
    {
        return $this->manager->getBusinessUnitRepo()->getBusinessUnitsCount();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
