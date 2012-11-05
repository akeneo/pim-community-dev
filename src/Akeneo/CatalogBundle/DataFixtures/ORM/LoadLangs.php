<?php
namespace Akeneo\CatalogBundle\DataFixtures\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Akeneo\CatalogBundle\Entity\Lang;
/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadLangs extends AbstractFixture implements ContainerAwareInterface
{

    /**
    * @var ContainerInterface
    */
    protected $container;
    
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\DependencyInjection\ContainerAwareInterface::setContainer()
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * (non-PHPdoc)
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $this->container->get('doctrine.orm.entity_manager');
        
        $this->loadLang(Lang::LANG_US, true);
        $this->loadLang(Lang::LANG_FR);
        
        $this->manager->flush();
        $this->manager->clear();
    }
    
    /**
     * Load a lang entity in database
     * @param string $locale
     * @param boolean $isDefault
     */
    protected function loadLang($locale, $isDefault = false)
    {
        $lang = new Lang();
        $lang->setLocale($locale);
        $lang->setIsDefault($isDefault);
        $this->manager->persist($lang);
    }
}