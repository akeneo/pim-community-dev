<?php

namespace Pim\Behat\Context;

use Behat\ChainedStepsExtension\Step;
use Behat\ChainedStepsExtension\Step\Then;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAware;
use SensioLabs\Behat\PageObjectExtension\PageObject\Factory as PageObjectFactory;

class NavigationContext extends PimContext implements PageObjectAware
{
    use SpinCapableTrait;

    /** @var string|null */
    public $currentPage;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var PageObjectFactory */
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
        'attribute groups'         => 'AttributeGroup index',
        'attribute group creation' => 'AttributeGroup creation',
        'dashboard'                => 'Dashboard index',
        'search'                   => 'Search index',
        'job tracker'              => 'JobTracker index',
        'my account'               => 'User profile',
        'clients'                  => 'Client index',
    ];

    /** @var array */
    protected $pageDecorators = [
        'Pim\Behat\Decorator\Page\GridCapableDecorator',
    ];

    protected $elements = [
        'Loading message' => ['css' => '#progressbar h3'],
    ];

    /**
     * @param string $mainContextClass
     * @param string $baseUrl
     */
    public function __construct(string $mainContextClass, string $baseUrl)
    {
        parent::__construct($mainContextClass);
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageObjectFactory(PageObjectFactory $pageFactory)
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
     * @param string      $username
     * @param string|null $password
     *
     * @Given /^I am logged in as "([^"]*)"( with password (?P<password>[^"]*))?$/
     */
    public function iAmLoggedInAs($username, ?string $password = null)
    {
        $this->getMainContext()->getSubcontext('fixtures')->setUsername($username);

        $this->getSession()->visit($this->locatePath('/user/logout'));

        $this->spin(function () {
            return $this->getSession()->getPage()->find('css', '.AknLogin-title');
        }, 'Cannot open the login page');

        $password = null !== $password ? $password : $username;
        $this->getSession()->getPage()->fillField('_username', $username);
        $this->getSession()->getPage()->fillField('_password', $password);

        $this->getSession()->getPage()->find('css', '.form-signin button')->press();

        $this->spin(function () {
            return $this->getSession()->getPage()->find('css', '.AknWidget');
        }, sprintf('Cannot reach Dashboard after login with %s', $username));
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
     * @param array $options
     *
     * @Given /^I am on the ([^"]*) page$/
     * @Given /^I go to the ([^"]*) page$/
     */
    public function iAmOnThePage($page, array $options = [])
    {
        $page = isset($this->getPageMapping()[$page]) ? $this->getPageMapping()[$page] : $page;

        $this->spin(function () use ($page, $options) {
            $this->openPage($page, $options);
            $expectedFullUrl = $this->getCurrentPage()->getUrl($options);
            $actualFullUrl = $this->getSession()->getCurrentUrl();
            $expectedUrl = $this->sanitizeUrl($expectedFullUrl);
            $actualUrl = $this->sanitizeUrl($actualFullUrl);

            return $expectedUrl === $actualUrl;
        }, sprintf('You are not on the %s page', $page));
    }

    /**
     * @param array $options
     *
     * @Given /^I am on the ([^"]*) grid$/
     * @Given /^I go to the ([^"]*) grid$/
     */
    public function iAmOnTheGrid($pageName, array $options = [])
    {
        $page = $this->getPageMapping()[$pageName];

        $this->openPage($page, $options);
        $this->spin(function () use ($page, $options) {
            $expectedFullUrl = $this->getCurrentPage()->getUrl();
            $actualFullUrl = $this->getSession()->getCurrentUrl();
            $expectedUrl = $this->sanitizeUrl($expectedFullUrl);
            $actualUrl = $this->sanitizeUrl($actualFullUrl);

            $result = $expectedUrl === $actualUrl;
            Assert::assertTrue($result, sprintf('Expecting to be on the grid %s, not %s', $expectedUrl, $actualUrl));

            return $this->getCurrentPage()->find('css', '.AknGridContainer');
        }, sprintf('You are not on the %s grid', $pageName));

        $this->wait();
    }

    /**
     * @param string $not
     * @param string $page
     *
     * @Given /^I should( not)? be able to access the ([^"]*) page$/
     */
    public function iShouldNotBeAbleToAccessThePage($not, $page)
    {
        $this->iAmOnThePage($page);

        if (!$not) {
            $this->getMainContext()->getSubcontext('assertions')->assertPageNotContainsText('Forbidden');
        } else {
            $this->getMainContext()->getSubcontext('assertions')->assertPageContainsText('Forbidden');
        }
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

        return new Step\Then('I should see "Forbidden"');
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I edit the "([^"]*)" (\w+)$/
     * @Given /^I am on the "([^"]*)" ((?!channel)(?!family)(?!attribute)\w+) page$/
     */
    public function iAmOnTheEntityEditPage($identifier, $page)
    {
        $this->spin(function () use ($identifier, $page) {
            $page   = ucfirst($page);
            $getter = sprintf('get%s', $page);
            $entity = $this->getFixturesContext()->$getter($identifier);
            $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);

            $expectedFullUrl = $this->getPage(sprintf('%s edit', $page))->getUrl(['id' => $entity->getId()]);

            $actualFullUrl = $this->getSession()->getCurrentUrl();
            $actualUrl     = $this->sanitizeUrl($actualFullUrl);
            $expectedUrl   = $this->sanitizeUrl($expectedFullUrl);
            $result        = $expectedUrl === $actualUrl;

            return true === $result;
        }, sprintf('Cannot got to the %s edit page', $page));
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I am on the "([^"]*)" (channel|family) page$/
     */
    public function iAmOnTheRedoEntityEditPage($identifier, $page)
    {
        $this->openPage(
            sprintf('%s edit', ucfirst($page)),
            ['code' => $identifier]
        );
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I wait to be on the "([^"]*)" (\w+) page$/
     */
    public function iWaitForTheEntityEditPage($identifier, $page)
    {
        $page   = ucfirst($page);
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->setCurrentPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);

        $this->wait();
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I show the "([^"]*)" ([\w ]+)$/
     * @Given /^I am on the "([^"]*)" ([\w ]+) show page$/
     */
    public function iAmOnTheEntityShowPage($identifier, $page)
    {
        $page = join('', array_map(function ($pageWord) {
            return ucfirst($pageWord);
        }, explode(' ', $page)));
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s show', $page), ['id' => $entity->getId()]);
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
     * @param string $code
     *
     * @Then /^I should be redirected on the (export|import) page of "([^"]*)"$/
     */
    public function iShouldBeRedirectedOnThePageOf($page, $code)
    {
        $page = str_replace('{code}', $code, $this->getPage(sprintf('%s show', ucfirst($page)))->getUrl());
        $this->assertAddress($page);
    }

    /**
     * @Given /^I refresh current page$/
     */
    public function iRefreshCurrentPage()
    {
        $this->getMainContext()->getSession()->reload();
        $this->wait();
    }

    /**
     * @Then /^I should see a nice loading message$/
     */
    public function iShouldSeeANiceLoadingMessage()
    {
        $messageNode = $this->spin(function () {
            return $this->getSession()
                ->getPage()
                ->find('css', $this->elements['Loading message']['css']);
        }, 'Unable to find any loading message');

        $message = trim($messageNode->getHtml());

        Assert::assertNotEquals('Loading ...', $message, 'The loading message should not equals the default value');
    }

    /**
     * @Then /^I should not see a nice loading message$/
     */
    public function iShouldNotSeeANiceLoadingMessage()
    {
        $messageNodeIsNull = $this->spin(function () {
            $node = $this->getSession()
                ->getPage()
                ->find('css', $this->elements['Loading message']['css']);
            return ($node === null);
        }, 'Found the loading message');

        Assert::assertEquals($messageNodeIsNull, true, 'The loading message should not be found');
    }

    /**
     * @param string  $pageName
     * @param array   $options
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function openPage($pageName, array $options = [])
    {
        $this->currentPage = $pageName;

        $page = $this->getCurrentPage()->open($options);

        return $page;
    }

    /**
     * @param string $pageName
     * @param array  $options
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function setCurrentPage($pageName, array $options = [])
    {
        $this->currentPage = $pageName;

        return $this->getCurrentPage();
    }

    /**
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    public function getCurrentPage()
    {
        $page = $this->getPage($this->currentPage);

        foreach ($this->pageDecorators as $decorator) {
            $page = new $decorator($page);
        }

        return $page;
    }

    /**
     * @param string $expectedFullUrl
     */
    public function assertAddress(string $expectedFullUrl)
    {
        $this->spin(function () use ($expectedFullUrl) {
            $actualFullUrl = $this->getSession()->getCurrentUrl();
            $actualUrl     = $this->sanitizeUrl($actualFullUrl);
            $expectedUrl   = $this->sanitizeUrl($expectedFullUrl);
            $result        = $expectedUrl === $actualUrl;

            Assert::assertTrue($result, sprintf('Expecting to be on page "%s", not "%s"', $expectedUrl, $actualUrl));

            return true;
        }, 'Spinning to assert address');
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
            $filteredUrl = $parsedUrl['fragment'];
        } else {
            $filteredUrl = $parsedUrl['path'];
        }

        if (false !== $urlWithoutLocale = strstr($filteredUrl, '?dataLocale=', true)) {
            $filteredUrl = $urlWithoutLocale;
        }

        if (false !== $urlWithoutRedirect = strstr($filteredUrl, '?redirectTab=', true)) {
            $filteredUrl = $urlWithoutRedirect;
        }

        if (false !== $urlWithoutGrid = strstr($filteredUrl, '|g/', true)) {
            $filteredUrl = $urlWithoutGrid;
        }

        if ((strlen($filteredUrl) - 1) === strpos($filteredUrl, '#')) {
            $filteredUrl = strstr($filteredUrl, '#', true);
        }

        return $filteredUrl;
    }

    /**
     * @deprecated This method is deprecated and should be removed avoid its use
     * @see For more information regarding to deprecation see TIP-442
     * @todo Delete method
     *
     * @param string $condition
     */
    protected function wait($condition = null)
    {
        $this->getMainContext()->wait($condition);
    }
}
