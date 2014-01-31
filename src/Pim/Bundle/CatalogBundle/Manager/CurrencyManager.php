<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Currency manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @param array $criterias
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getCurrencies($criterias = array())
    {
        return $this->objectManager->getRepository('PimCatalogBundle:Currency')->findBy($criterias);
    }

    /**
     * Get active codes
     *
     * @return string[]
     */
    public function getActiveCodes()
    {
        return array_map(
            function ($value) {
                return $value->getCode();
            },
            $this->getActiveCurrencies()
        );
    }

    /**
     * Get active code choices
     *
     * Prior to PHP 5.4 array_combine() does not accept
     * empty array as argument.
     *
     * @see http://php.net/array_combine#refsect1-function.array-combine-changelog
     *
     * @return array
     */
    public function getActiveCodeChoices()
    {
        $codes = $this->getActiveCodes();

        if (empty($codes)) {
            return array();
        }

        return array_combine($codes, $codes);
    }
}
