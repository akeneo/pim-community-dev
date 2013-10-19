<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;

/**
 * Context for navigating the website
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NavigationContext extends RawMinkContext implements PageObjectAwareInterface
{
    /**
     * @var string|null $currentPage
     */
    public $currentPage = null;

    /**
     * @var PageFactory $pageFactory
     */
    private $pageFactory = null;

    /**
     * @var array $pageMapping
     */
    private $pageMapping = array(
        'associations'             => 'Association index',
        'attributes'               => 'Attribute index',
        'categories'               => 'Category tree creation',
        'channels'                 => 'Channel index',
        'currencies'               => 'Currency index',
        'exports'                  => 'Export index',
        'families'                 => 'Family index',
        'home'                     => 'Base index',
        'imports'                  => 'Import index',
        'locales'                  => 'Locale index',
        'products'                 => 'Product index',
        'users'                    => 'User index',
        'user roles'               => 'UserRole index',
        'user groups'              => 'UserGroup index',
        'variants'                 => 'Variant index',
        'attribute groups'         => 'AttributeGroup index',
        'attribute group creation' => 'AttributeGroup creation',
    );

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @BeforeScenario
     */
    public function resetCurrentPage()
    {
        $this->currentPage = null;
    }

    /**
     * @param string $page
     *
     * @Given /^I am on the ([^"]*) page$/
     */
    public function iAmOnThePage($page)
    {
        $page = isset($this->pageMapping[$page]) ? $this->pageMapping[$page] : $page;
        $this->openPage($page);
        $this->wait();
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I edit the "([^"]*)" (\w+)$/
     * @Given /^I am on the "([^"]*)" (\w+) page$/
     */
    public function iAmOnTheEntityEditPage($identifier, $page)
    {
        $page = ucfirst($page);
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param string $name
     *
     * @return Page
     */
    public function getPage($name)
    {
        if (null === $this->pageFactory) {
            throw new \RuntimeException('To create pages you need to pass a factory with setPageFactory()');
        }

        $name = implode('\\', array_map('ucfirst', explode(' ', $name)));

        return $this->pageFactory->createPage($name);
    }

    /**
     * @param string $page
     * @param array  $options
     *
     * @return Page
     */
    public function openPage($page, array $options = array())
    {
        $this->currentPage = $page;

        $page = $this->getCurrentPage()->open($options);
        $this->wait();

        return $page;
    }

    /**
     * @return Page
     */
    public function getCurrentPage()
    {
        return $this->getPage($this->currentPage);
    }

    /**
     * @param integer $time
     * @param string  $condition
     *
     * @return void
     */
    private function wait($time = 5000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }

    /**
     * @return FixturesContext
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
