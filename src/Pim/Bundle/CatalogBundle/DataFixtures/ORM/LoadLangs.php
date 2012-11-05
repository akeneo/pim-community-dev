<?php
namespace Pim\Bundle\CatalogBundle\DataFixtures\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Pim\Bundle\CatalogBundle\Entity\Lang;
/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadLangs extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager)
    {
        $this->loadLang($manager, Lang::LANG_US, true);
        $this->loadLang($manager, Lang::LANG_FR);

        $manager->flush();
        $manager->clear();
    }

    /**
     * Load a lang entity in database
     * @param string $locale
     * @param boolean $isDefault
     */
    protected function loadLang($manager, $locale, $isDefault = false)
    {
        $lang = new Lang();
        $lang->setLocale($locale);
        $lang->setIsDefault($isDefault);
        $manager->persist($lang);
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 2;
    }
}