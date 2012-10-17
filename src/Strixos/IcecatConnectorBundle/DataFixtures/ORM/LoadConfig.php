<?php
namespace Akeneo\CatalogBundle\DataFixtures\ORM;

use Strixos\IcecatConnectorBundle\Entity\Config;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\CatalogBundle\Model\ProductType;
use Akeneo\CatalogBundle\Model\BaseFieldFactory;

/**
 * Load base configuration for module
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadConfig extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
    * @var ContainerInterface
    */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        
        $this->loadConfig(Config::LANGUAGES_FILE, 'languages-list.xml');
        $this->loadConfig(Config::LANGUAGES_URL, 'https://data.icecat.biz/export/freexml/refs/LanguageList.xml.gz');
        
        $this->loadConfig(Config::PRODUCT_FILE, 'product-%%product_id%%-%%locale%%.xml');
        $this->loadConfig(Config::PRODUCT_URL, 'http://data.Icecat.biz/xml_s3/xml_server3.cgi');
        
        $this->loadConfig(Config::PRODUCTS_FILE, 'export_urls_rich.txt');
        $this->loadConfig(Config::PRODUCTS_URL, 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz');
        
        $this->loadConfig(Config::SUPPLIERS_FILE, 'suppliers-list.xml');
        $this->loadConfig(Config::SUPPLIERS_URL, 'http://data.icecat.biz/export/freeurls/supplier_mapping.xml');
        
        $this->manager->flush();
        $this->manager->clear();
    }
    
    /**
     * Load a config entity in database
     * @param string $code
     * @param mixed $value
     */
    protected function loadConfig($code, $value)
    {
        $config = new Config();
        $config->setCode($code);
        $config->setValue($value);
        $this->manager->persist($config);
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 1;
    }

}