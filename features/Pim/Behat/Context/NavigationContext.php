<?php

namespace Pim\Behat\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Context\Page\Base\Base;
use Context\Spin\SpinCapableTrait;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;

class NavigationContext extends PimContext implements PageObjectAwareInterface
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
     * @return array
     */
    public function getPageMapping()
    {
        return $this->pageMapping;
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
     * @param string $path
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
     * @throws \Exception
     *
     * @return null|Then
     *
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
     * @param string $expected
     */
    public function assertAddress($expected)
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
     * TODO: should be deleted
     *
     * @param int    $time
     * @param string $condition
     */
    protected function wait($time = 10000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }
}
