<?php

namespace Pim\Bundle\ConfigBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Currency manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * Constructor
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get active currencies
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getActiveCurrencies()
    {
        $criterias = array('activated' => true);

        return $this->getCurrencies($criterias);
    }

    /**
     * Get currencies with criterias
     *
     * @param multitype:string $criterias
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getCurrencies($criterias = array())
    {
        return $this->objectManager->getRepository('PimConfigBundle:Currency')->findBy($criterias);
    }

    /**
     * Get active codes
     *
     * @return multitype:string
     */
    public function getActiveCodes()
    {
        $codes = array();
        foreach ($this->getActiveCurrencies() as $currency) {
            $codes[] = $currency->getCode();
        }

        return $codes;
    }
}
