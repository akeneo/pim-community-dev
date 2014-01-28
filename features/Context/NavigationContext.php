<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Step;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Model\Product;

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
     * @var string $username
     */
    private $username = null;

    /**
     * @var string $password
     */
    private $password = null;

    /**
     * @var PageFactory $pageFactory
     */
    private $pageFactory = null;

    /**
     * @var array $pageMapping
     */
    private $pageMapping = array(
        'association types'        => 'AssociationType index',
        'attributes'               => 'Attribute index',
        'categories'               => 'Category tree creation',
        'channels'                 => 'Channel index',
        'currencies'               => 'Currency index',
        'exports'                  => 'Export index',
        'export executions'        => 'ExportExecution index',
        'families'                 => 'Family index',
        'home'                     => 'Base index',
        'imports'                  => 'Import index',
        'import executions'        => 'ImportExecution index',
        'locales'                  => 'Locale index',
        'products'                 => 'Product index',
        'product groups'           => 'ProductGroup index',
        'group types'              => 'GroupType index',
        'users'                    => 'User index',
        'user roles'               => 'UserRole index',
        'user groups'              => 'UserGroup index',
        'variant groups'           => 'VariantGroup index',
        'attribute groups'         => 'AttributeGroup index',
        'attribute group creation' => 'AttributeGroup creation',
        'dashboard'                => 'Dashboard index',
        'search'                   => 'Search index',
    );

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @AfterScenario
     */
    public function resetCurrentPage()
    {
        $script = 'sessionStorage.clear(); typeof $ !== "undefined" && $(window).off("beforeunload");';
        $this->getMainContext()->executeScript($script);
        $this->currentPage = null;
    }

    /**
     * @param string $username
     *
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $this->username = $username;
        $this->password = $username;
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
     * @param string $not
     * @param string $page
     *
     * @return null|Then
     * @Given /^I should( not)? be able to access the ([^"]*) page$/
     */
    public function iShouldNotBeAbleToAccessThePage($not, $page)
    {
        if (!$not) {
            return $this->iAmOnThePage($page);
        }

        $page = isset($this->pageMapping[$page]) ? $this->pageMapping[$page] : $page;

        $this->currentPage = $page;
        $this->getCurrentPage()->open();

        return new Step\Then('I should see "403 Forbidden"');
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
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" attribute group page$/
     * @Given /^I edit the "([^"]*)" attribute group$/
     */
    public function iAmOnTheAttributeGroupEditPage($identifier)
    {
        $page = 'AttributeGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param string $identifier
     *
     * @Given /^I edit the "([^"]*)" association type$/
     */
    public function iEditTheAssociationType($identifier)
    {
        $page = 'AssociationType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param Category $category
     *
     * @Given /^I am on the (category "([^"]*)") node creation page$/
     */
    public function iAmOnTheCategoryNodeCreationPage(Category $category)
    {
        $this->openPage('Category node creation', array('id' => $category->getId()));
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) page$/
     */
    public function iAmOnTheImportJobPage(JobInstance $job)
    {
        $this->openPage('Import show', array('id' => $job->getId()));
        $this->wait();
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) page$/
     */
    public function iAmOnTheExportJobPage(JobInstance $job)
    {
        $this->openPage('Export show', array('id' => $job->getId()));
        $this->wait();
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) edit page$/
     */
    public function iAmOnTheImportJobEditPage(JobInstance $job)
    {
        $this->openPage('Import edit', array('id' => $job->getId()));
        $this->wait();
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) edit page$/
     */
    public function iAmOnTheExportJobEditPage(JobInstance $job)
    {
        $this->openPage('Export edit', array('id' => $job->getId()));
        $this->wait();
    }

    /**
     * @param string $jobTitle
     * @param string $jobType
     *
     * @Given /^I create a new "([^"]*)" (import|export)$/
     */
    public function iCreateANewJob($jobTitle, $jobType)
    {
        $jobType = ucfirst($jobType);
        $this->getPage(sprintf('%s index', $jobType))->clickJobCreationLink($jobTitle);
        $this->wait();
        $this->currentPage = sprintf('%s creation', $jobType);
    }

    /**
     * @param string $jobType
     *
     * @Given /^I try to create an unknown (import|export)$/
     */
    public function iTryToCreateAnUnknownJob($jobType)
    {
        $this->openPage(sprintf('%s creation', ucfirst($jobType)));
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" product group page$/
     * @Given /^I edit the "([^"]*)" product group$/
     */
    public function iAmOnTheProductGroupEditPage($identifier)
    {
        $page = 'ProductGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" variant group page$/
     * @Given /^I edit the "([^"]*)" variant group$/
     */
    public function iAmOnTheVariantGroupEditPage($identifier)
    {
        $page = 'VariantGroup';
        $entity = $this->getFixturesContext()->getProductGroup($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" group type page$/
     * @Given /^I edit the "([^"]*)" group type$/
     */
    public function iAmOnTheGroupTypeEditPage($identifier)
    {
        $page = 'GroupType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" association type page$/
     */
    public function iAmOnTheAssociationTypeEditPage($identifier)
    {
        $page = 'AssociationType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
    }

    /**
     * @param JobInstance $job
     *
     * @When /^I launch the ("([^"]*)" export job)$/
     */
    public function iLaunchTheExportJob(JobInstance $job)
    {
        $this->openPage('Export launch', array('id' => $job->getId()));
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
     *
     * @Then /^I should be redirected on the (.*) page$/
     */
    public function iShouldBeRedirectedOnThePage($page)
    {
        $this->assertAddress($this->getPage($page)->getUrl());
    }

    /**
     * @param AttributeGroup $group
     *
     * @Given /^I should be on the ("([^"]*)" attribute group) page$/
     */
    public function iShouldBeOnTheAttributeGroupPage(AttributeGroup $group)
    {
        $expectedAddress = $this->getPage('AttributeGroup edit')->getUrl(array('id' => $group->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param GroupType $groupType
     *
     * @Given /^I should be on the ("([^"]*)" group type) page$/
     */
    public function iShouldBeOnTheGroupTypePage(GroupType $groupType)
    {
        $expectedAddress = $this->getPage('GroupType edit')->getUrl(array('id' => $groupType->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Role $role
     *
     * @Given /^I should be on the ("([^"]*)" role) page$/
     */
    public function iShouldBeOnTheRolePage(Role $role)
    {
        $expectedAddress = $this->getPage('Role edit')->getUrl(array('id' => $role->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Family $family
     *
     * @Given /^I should be on the ("([^"]*)" family) page$/
     */
    public function iShouldBeOnTheFamilyPage(Family $family)
    {
        $expectedAddress = $this->getPage('Family edit')->getUrl(array('id' => $family->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param AssociationType $associationType
     *
     * @Given /^I should be on the ("([^"]*)" association type) page$/
     */
    public function iShouldBeOnTheAssociationTypePage(AssociationType $associationType)
    {
        $expectedAddress = $this->getPage('AssociationType edit')->getUrl(array('id' => $associationType->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the locales page$/
     */
    public function iShouldBeOnTheLocalesPage()
    {
        $this->assertAddress($this->getPage('Locale index')->getUrl());
    }

    /**
     * @param Category $category
     *
     * @Then /^I should be on the (category "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheCategoryEditPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category edit')->getUrl(array('id' => $category->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Category $category
     *
     * @Given /^I should be on the (category "([^"]*)") node creation page$/
     */
    public function iShouldBeOnTheCategoryNodeCreationPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category node creation')->getUrl(array('id' => $category->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the products page$/
     */
    public function iShouldBeOnTheProductsPage()
    {
        $expectedAddress = $this->getPage('Product index')->getUrl();
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Product $product
     *
     * @Given /^I should be on the (product "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheProductEditPage(Product $product)
    {
        $expectedAddress = $this->getPage('Product edit')->getUrl(array('id' => $product->getId()));
        $this->assertAddress($expectedAddress);
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
        $this->loginIfRequired();
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
     * @param string $expected
     */
    private function assertAddress($expected)
    {
        $actual = $this->getSession()->getCurrentUrl();
        $result = strpos($actual, $expected) !== false;
        assertTrue($result, sprintf('Expecting to be on page "%s", not "%s"', $expected, $actual));
    }

    /**
     * A method that logs the user in with the previously provided credentials if required by the page
     */
    private function loginIfRequired()
    {
        $loginForm = $this->getCurrentPage()->find('css', '.form-signin');
        if ($loginForm) {
            $loginForm->fillField('_username', $this->username);
            $loginForm->fillField('_password', $this->password);
            $loginForm->pressButton('Log in');
        }
    }

    /**
     * @param integer $time
     * @param string  $condition
     *
     * @return void
     */
    private function wait($time = 10000, $condition = null)
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
