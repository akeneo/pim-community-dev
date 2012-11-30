<?php
namespace Pim\Bundle\CatalogBundle\DataFixtures\ORM;

use Pim\Bundle\ConnectorIcecatBundle\Entity\Config;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load base configuration for module
 *
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadConfig extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
    * @var ContainerInterface
    */
    protected $container;

    /**
     * @var ObjectManager
     */
    protected $manager;

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

        $this->loadConfig(Config::LOGIN, 'NicolasDupont');
        $this->loadConfig(Config::PASSWORD, '1cec4t**)');

        $this->loadConfig(Config::BASE_DIR, '/tmp/');
        $this->loadConfig(Config::BASE_URL, 'http://data.icecat.biz/');

        $this->loadConfig(Config::BASE_PRODUCTS_URL, 'export/freexml.int/');

        $this->loadConfig(Config::CATEGORIES_FILE, 'categories-list.xml');
        $this->loadConfig(Config::CATEGORIES_ARCHIVED_FILE, 'categories-list.xml.gz');
        $this->loadConfig(Config::CATEGORIES_URL, 'http://data.icecat.biz/export/freexml/refs/CategoriesList.xml.gz');

        $this->loadConfig(Config::LANGUAGES_FILE, 'languages-list.xml');
        $this->loadConfig(Config::LANGUAGES_ARCHIVED_FILE, 'languages-list.xml.gz');
        $this->loadConfig(Config::LANGUAGES_URL, 'http://data.icecat.biz/export/freexml/refs/LanguageList.xml.gz');

        $this->loadConfig(Config::PRODUCT_FILE, 'product-%%product_id%%-%%locale%%.xml');
        $this->loadConfig(Config::PRODUCT_ARCHIVED_FILE, 'product-%%product_id%%-%%locale%%.xml.gz');
        $this->loadConfig(Config::PRODUCT_URL, 'http://data.icecat.biz/xml_s3/xml_server3.cgi');

        $this->loadConfig(Config::PRODUCTS_FILE, 'export_urls_rich.txt');
        $this->loadConfig(Config::PRODUCTS_ARCHIVED_FILE, 'export_urls_rich.txt.gz');
        $this->loadConfig(Config::PRODUCTS_URL, 'http://data.icecat.biz/export/freeurls/export_urls_rich.txt.gz');

        $this->loadConfig(Config::SUPPLIERS_FILE, 'suppliers-list.xml');
        $this->loadConfig(Config::SUPPLIERS_URL, 'http://data.icecat.biz/export/freeurls/supplier_mapping.xml');

        $this->manager->flush();
        $this->manager->clear();
    }

    /**
     * Load a config entity in a database
     *
     * @param string $code  my code
     * @param string $value my value
     */
    protected function loadConfig($code, $value)
    {
        $config = new Config();
        $config->setCode($code);
        $config->setValue($value);
        $this->manager->persist($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

}
