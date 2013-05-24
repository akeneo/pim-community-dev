<?php
namespace Pim\Bundle\ConfigBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Locale manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleManager
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
     * Get active locales
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getActiveLocales()
    {
        $criterias = array('activated' => true);

        return $this->getLocales($criterias);
    }

    /**
     * Get disabled locales
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getDisabledLocales()
    {
        $criterias = array('activated' => false);

        return $this->getLocales($criterias);
    }

    /**
     * Get locales with criterias
     *
     * @param multitype:string $criterias
     *
     * @return \Doctrine\Common\Persistence\mixed
     */
    public function getLocales($criterias = array())
    {
        return $this->objectManager->getRepository('PimConfigBundle:Locale')->findBy($criterias);
    }

    /**
     * Get active codes
     *
     * @return multitype:string
     */
    public function getActiveCodes()
    {
        $codes = array();
        foreach ($this->getActiveLocales() as $locale) {
            $codes[] = $locale->getCode();
        }

        return $codes;
    }
}
