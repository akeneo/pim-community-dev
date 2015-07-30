<?php

namespace Akeneo\Bundle\MeasureBundle\Family;

/**
 * Amperage family
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ElectricChargeFamilyInterface
{
    
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'ElectricCharge';
    
    /**
     * @staticvar string
     */
    const AMPEREHOUR = 'AMPEREHOUR';

    /**
     * @staticvar string
     */
    const MILLIAMPEREHOUR = 'MILLIAMPEREHOUR';

    /**
     * @staticvar string
     */
    const MILLICOULOMB = 'MILLICOULOMB';

    /**
     * @staticvar string
     */
    const CENTICOULOMB = 'CENTICOULOMB';

    /**
     * @staticvar string
     */
    const DECICOULOMB = 'DECICOULOMB';

    /**
     * @staticvar string
     */
    const COULOMB = 'COULOMB';

    /**
     * @staticvar string
     */
    const DEKACOULOMB = 'DEKACOULOMB';

    /**
     * @staticvar string
     */
    const HECTOCOULOMB = 'HECTOCOULOMB';

    /**
     * @staticvar string
     */
    const KILOCOULOMB = 'KILOCOULOMB';

    /**
     * @staticvar string
     */
    const MEGACOULOMB = 'MEGACOULOMB';
}
