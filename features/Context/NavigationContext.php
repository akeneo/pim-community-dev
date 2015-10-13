<?php

namespace Context;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Behat\Behat\Event\BaseScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Page\Base\Base;
use Context\Spin\SpinCapableTrait;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\Product;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;

/**
 * Context for navigating the website
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NavigationContext extends RawMinkContext implements PageObjectAwareInterface
{
    use SpinCapableTrait;

    /** @var string|null */
    public $currentPage;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var PageFactory */
    protected $pageFactory;

    /** @var string */
    protected $baseUrl;

    /** @var array */
    protected $pageMapping = [
        'association types'        => 'AssociationType index',
        'attributes'               => 'Attribute index',
        'categories'               => 'Category tree index',
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
        'user roles creation'      => 'UserRole creation',
        'user groups'              => 'UserGroup index',
        'user groups creation'     => 'UserGroup creation',
        'user groups edit'         => 'UserGroup edit',
        'variant groups'           => 'VariantGroup index',
        'attribute groups'         => 'AttributeGroup index',
        'attribute group creation' => 'AttributeGroup creation',
        'dashboard'                => 'Dashboard index',
        'search'                   => 'Search index',
        'job tracker'              => 'JobTracker index',
        'my account'               => 'User profile',
    ];

    /**
     * @param string $baseUrl
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @param BaseScenarioEvent $event
     *
     * @AfterScenario
     */
    public function resetCurrentPage(BaseScenarioEvent $event)
    {
        if ($event->getResult() !== StepEvent::UNDEFINED) {
            $script = 'sessionStorage.clear(); typeof $ !== "undefined" && $(window).off("beforeunload");';
            $this->getMainContext()->executeScript($script);
        }

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

        $this->getMainContext()->getSubcontext('fixtures')->setUsername($username);
    }

    /**
     * @Given /^I logout$/
     */
    public function iLogout()
    {
        $this->getSession()->visit($this->locatePath('/user/logout'));
    }

    /**
     * @param string $code
     *
     * @When /^I go on the last executed job resume of "([^"]*)"$/
     */
    public function iGoOnTheLastExecutedJobResume($code)
    {
        $this->wait();
        $jobInstance   = $this->getFixturesContext()->getJobInstance($code);
        $jobExecutions = $jobInstance->getJobExecutions();

        $url = $this->getPage('MassEditJob show')->getUrl(['id' => $jobExecutions->last()->getId()]);
        $this->getSession()->visit($this->locatePath($url));
        $this->wait();
    }

    /**
     * @param string $page
     *
     * @Given /^I am on the ([^"]*) page$/
     * @Given /^I go to the ([^"]*) page$/
     */
    public function iAmOnThePage($page)
    {
        $page = isset($this->getPageMapping()[$page]) ? $this->getPageMapping()[$page] : $page;
        $this->openPage($page);
    }

    /**
     * @param string $page
     * @param string $referer
     *
     * @Given /^I am on the relative path ([^"]+) from ([^"]+)$/
     */
    public function iAmOnTheRelativePath($path, $referer)
    {
        $basePath = parse_url($this->baseUrl)['path'];
        $uri = sprintf('%s%s/#url=%s%s', $this->baseUrl, $referer, $basePath, $path);

        $this->getSession()->visit($uri);
    }

    /**
     * @param string $not
     * @param string $page
     *
     * @return null|Step\Then
     * @Given /^I should( not)? be able to access the ([^"]*) page$/
     */
    public function iShouldNotBeAbleToAccessThePage($not, $page)
    {
        if (!$not) {
            return $this->iAmOnThePage($page);
        }

        $page = isset($this->getPageMapping()[$page]) ? $this->getPageMapping()[$page] : $page;

        $this->currentPage = $page;
        $this->getCurrentPage()->open();

        return new Step\Then('I should see "403 Forbidden"');
    }

    /**
     * @param string $not
     * @param string $action
     * @param string $identifier
     * @param string $page
     *
     * @return null|Then
     * @Given /^I should( not)? be able to (\w+) the "([^"]*)" (\w+)$/
     * @Given /^I should( not)? be able to access the (\w+) "([^"]*)" (\w+) page$/
     */
    public function iShouldNotBeAbleToAccessTheEntityEditPage($not, $action, $identifier, $page)
    {
        if (null === $action) {
            $action = 'edit';
        }

        if (!$not) {
            if ('edit' === $action) {
                $this->iAmOnTheEntityEditPage($identifier, $page);
            } elseif ('show' === $action) {
                $this->iAmOnTheEntityShowPage($identifier, $page);
            } else {
                throw new \Exception('Action "%s" is not handled yet.');
            }

            return null;
        }

        $page   = ucfirst($page);
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);

        $this->currentPage = sprintf('%s %s', $page, $action);
        $this->getCurrentPage()->open(['id' => $entity->getId()]);

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
        $page   = ucfirst($page);
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
        $this->wait();
    }

    /**
     * @param string $identifier
     *
     * @Given /^I edit the "([^"]*)" user group$/
     */
    public function iEditTheUserGroup($identifier)
    {
        $page   = 'UserGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('UserGroup edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $label
     *
     * @Given /^I edit the "([^"]+)" user role$/
     */
    public function iEditTheUserRole($label)
    {
        $page   = 'UserRole';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($label);
        $this->openPage(sprintf('UserRole edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I show the "([^"]*)" (\w+)$/
     * @Given /^I am on the "([^"]*)" (\w+) show page$/
     */
    public function iAmOnTheEntityShowPage($identifier, $page)
    {
        $page   = ucfirst($page);
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s show', $page), ['id' => $entity->getId()]);
        $this->wait();
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" attribute group page$/
     * @Given /^I edit the "([^"]*)" attribute group$/
     */
    public function iAmOnTheAttributeGroupEditPage($identifier)
    {
        $page   = 'AttributeGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I edit the "([^"]*)" association type$/
     */
    public function iEditTheAssociationType($identifier)
    {
        $page   = 'AssociationType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param Category $category
     *
     * @Given /^I am on the (category "([^"]*)") node creation page$/
     */
    public function iAmOnTheCategoryNodeCreationPage(Category $category)
    {
        $this->openPage('Category node creation', ['id' => $category->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) page$/
     */
    public function iAmOnTheImportJobPage(JobInstance $job)
    {
        $this->openPage('Import show', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) page$/
     */
    public function iAmOnTheExportJobPage(JobInstance $job)
    {
        $this->openPage('Export show', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) edit page$/
     */
    public function iAmOnTheImportJobEditPage(JobInstance $job)
    {
        $this->openPage('Import edit', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) edit page$/
     */
    public function iAmOnTheExportJobEditPage(JobInstance $job)
    {
        $this->openPage('Export edit', ['id' => $job->getId()]);
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
        $page   = 'ProductGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" variant group page$/
     * @Given /^I edit the "([^"]*)" variant group$/
     */
    public function iAmOnTheVariantGroupEditPage($identifier)
    {
        $page   = 'VariantGroup';
        $entity = $this->getFixturesContext()->getProductGroup($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" group type page$/
     * @Given /^I edit the "([^"]*)" group type$/
     */
    public function iAmOnTheGroupTypeEditPage($identifier)
    {
        $page   = 'GroupType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" association type page$/
     */
    public function iAmOnTheAssociationTypeEditPage($identifier)
    {
        $page   = 'AssociationType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @When /^I launch the ("([^"]*)" (import|export) job)$/
     */
    public function iLaunchTheExportJob(JobInstance $job)
    {
        $jobType = ucfirst($job->getType());
        $this->openPage(sprintf('%s launch', $jobType), ['id' => $job->getId()]);
    }

    /**
     * @param string      $action
     * @param JobInstance $job
     *
     * @return \Behat\Behat\Context\Step\Then
     *
     * @When /^I should not be able to (launch|edit) the ("([^"]*)" (export|import) job)$/
     */
    public function iShouldNotBeAbleToAccessTheJob($action, JobInstance $job)
    {
        $this->currentPage = sprintf("%s %s", ucfirst($job->getType()), $action);
        $page              = $this->getCurrentPage()->open(['id' => $job->getId()]);

        return new Step\Then('I should see "403 Forbidden"');
    }

    /**
     * @param string $name
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
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
        $page = isset($this->getPageMapping()[$page]) ? $this->getPageMapping()[$page] : $page;
        $this->assertAddress($this->getPage($page)->getUrl());
    }

    /**
     * @param AttributeGroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" attribute group) page$/
     */
    public function iShouldBeOnTheAttributeGroupPage(AttributeGroupInterface $group)
    {
        $expectedAddress = $this->getPage('AttributeGroup edit')->getUrl(['id' => $group->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I should be on the ("([^"]*)" (import|export) job) page$/
     */
    public function iShouldBeOnTheJobPage(JobInstance $job)
    {
        $jobPage         = sprintf('%s show', ucfirst($job->getType()));
        $expectedAddress = $this->getPage($jobPage)->getUrl(['id' => $job->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param GroupTypeInterface $groupType
     *
     * @Given /^I should be on the ("([^"]*)" group type) page$/
     */
    public function iShouldBeOnTheGroupTypePage(GroupTypeInterface $groupType)
    {
        $expectedAddress = $this->getPage('GroupType edit')->getUrl(['id' => $groupType->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param GroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" product group) page$/
     */
    public function iShouldBeOnTheProductGroupPage(GroupInterface $group)
    {
        $expectedAddress = $this->getPage('ProductGroup edit')->getUrl(['id' => $group->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param GroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" variant group) page$/
     */
    public function iShouldBeOnTheVariantGroupPage(GroupInterface $group)
    {
        $expectedAddress = $this->getPage('VariantGroup edit')->getUrl(['id' => $group->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Role $role
     *
     * @Given /^I should be on the ("([^"]*)" role) page$/
     */
    public function iShouldBeOnTheRolePage(Role $role)
    {
        $expectedAddress = $this->getPage('Role edit')->getUrl(['id' => $role->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Then /^I should be on the user groups edit page$/
     */
    public function iShouldBeOnTheUserGroupsEditPage()
    {
        $this->assertAddress($this->getPage('UserGroup edit')->getUrl());
    }

    /**
     * @param Family $family
     *
     * @Given /^I should be on the ("([^"]*)" family) page$/
     */
    public function iShouldBeOnTheFamilyPage(Family $family)
    {
        $expectedAddress = $this->getPage('Family edit')->getUrl(['id' => $family->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param AssociationTypeInterface $associationType
     *
     * @Given /^I should be on the ("([^"]*)" association type) page$/
     */
    public function iShouldBeOnTheAssociationTypePage(AssociationTypeInterface $associationType)
    {
        $expectedAddress = $this->getPage('AssociationType edit')->getUrl(['id' => $associationType->getId()]);
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
     * @Given /^I should be on the families page$/
     */
    public function iShouldBeOnTheFamiliesPage()
    {
        $this->assertAddress($this->getPage('Family index')->getUrl());
    }

    /**
     * @Given /^I should be on the attributes page$/
     */
    public function iShouldBeOnTheAttributesPage()
    {
        $this->assertAddress($this->getPage('Attribute index')->getUrl());
    }

    /**
     * @Given /^I should be on the categories page$/
     */
    public function iShouldBeOnTheCategoriesPage()
    {
        $this->assertAddress($this->getPage('Category index')->getUrl());
    }

    /**
     * @param Category $category
     *
     * @Then /^I should be on the (category "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheCategoryEditPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category edit')->getUrl(['id' => $category->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Category $category
     *
     * @Given /^I should be on the (category "([^"]*)") node creation page$/
     */
    public function iShouldBeOnTheCategoryNodeCreationPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category node creation')->getUrl(['id' => $category->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the products page$/
     */
    public function iShouldBeOnTheProductsPage()
    {
        $expectedAddress = $this->getPage('Product index')->getUrl();
        $this->assertAddress($expectedAddress);
        $this->wait();
    }

    /**
     * @param Product $product
     *
     * @Given /^I should be on the (product "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheProductEditPage(Product $product)
    {
        $this->spin(function () use ($product) {
            $expectedAddress = $this->getPage('Product edit')->getUrl(['id' => $product->getId()]);
            $this->assertAddress($expectedAddress);

            return true;
        });

        $this->getMainContext()->spin(function () {
            return $this->getCurrentPage()->find('css', '.product-label');
        });
    }

    /**
     * @Given /^I refresh current page$/
     */
    public function iRefreshCurrentPage()
    {
        $this->getMainContext()->reload();
        $this->wait();
    }

    /**
     * @When /^I pin the current page$/
     */
    public function iPinTheCurrentPage()
    {
        $pinButton = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.minimize-button');
        }, 10);

        $pinButton->click();
    }

    /**
     * @When /^I click on the pinned item "([^"]+)"$/
     *
     * @param string $label
     */
    public function iClickOnThePinnedItem($label)
    {
        $pinnedItem = $this->spin(function () use ($label) {
            return $this->getCurrentPage()->find('css', sprintf('.pin-bar a[title="%s"]', $label));
        }, 10);

        $pinnedItem->click();
    }

    /**
     * @param string $page
     * @param array  $options
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function openPage($page, array $options = [])
    {
        $this->currentPage = $page;

        /** @var Base $page */
        $page = $this->getCurrentPage()->open($options);

        // spin function to deal with invalid CSRF problems
        $this->getMainContext()->spin(function () use ($page, $options) {
            if ($this->loginIfRequired()) {
                $page = $this->getCurrentPage()->open($options);
                $this->wait();
            }

            return $page->verifyAfterLogin();
        }, 30);
        $this->wait();

        return $page;
    }

    /**
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function getCurrentPage()
    {
        return $this->getPage($this->currentPage);
    }

    /**
     * @return array
     */
    public function getPageMapping()
    {
        return $this->pageMapping;
    }

    /**
     * @param string $expected
     */
    protected function assertAddress($expected)
    {
        $actualFullUrl = $this->getSession()->getCurrentUrl();
        $actualUrl     = $this->sanitizeUrl($actualFullUrl);

        $result = parse_url($expected, PHP_URL_PATH) === $actualUrl;

        assertTrue($result, sprintf('Expecting to be on page "%s", not "%s"', $expected, $actualUrl));
    }

    /**
     * Sanitize an url to return the clean it without scheme, host, data locale and grid params
     *
     * @param string $fullUrl
     *
     * @return string
     */
    protected function sanitizeUrl($fullUrl)
    {
        $parsedUrl = parse_url($fullUrl);

        if (isset($parsedUrl['fragment'])) {
            $filteredUrl = preg_split('/url=/', $parsedUrl['fragment'])[1];
        } else {
            $filteredUrl = $parsedUrl['path'];
        }

        if (false !== $urlWithoutLocale = strstr($filteredUrl, '?dataLocale=', true)) {
            $filteredUrl = $urlWithoutLocale;
        }

        if (false !== $urlWithoutGrid = strstr($filteredUrl, '|g/', true)) {
            $filteredUrl = $urlWithoutGrid;
        }

        return $filteredUrl;
    }

    /**
     * A method that logs the user in with the previously provided credentials if required by the page
     *
     * @return bool true if login was required, false if not
     */
    protected function loginIfRequired()
    {
        $loginForm = $this->getCurrentPage()->find('css', '.form-signin');
        if ($loginForm) {
            $loginForm->fillField('_username', $this->username);
            $loginForm->fillField('_password', $this->password);
            $loginForm->pressButton('Log in');

            return true;
        }

        return false;
    }

    /**
     * @param int    $time
     * @param string $condition
     */
    protected function wait($time = 10000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }

    /**
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
