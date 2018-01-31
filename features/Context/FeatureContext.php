<?php

namespace Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Testwork\Counter\Exception\TimerException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Context\AttributeValidationContext;
use Pim\Behat\Context\Domain\Collect\ImportProfilesContext;
use Pim\Behat\Context\Domain\Enrich\AttributeTabContext;
use Pim\Behat\Context\Domain\Enrich\CompletenessContext;
use Pim\Behat\Context\Domain\Enrich\FamilyVariantConfigurationContext;
use Pim\Behat\Context\Domain\Enrich\GridPaginationContext;
use Pim\Behat\Context\Domain\Enrich\ProductGroupContext;
use Pim\Behat\Context\Domain\SecondaryActionsContext;
use Pim\Behat\Context\Domain\Spread\ExportBuilderContext;
use Pim\Behat\Context\Domain\Spread\ExportProfilesContext;
use Pim\Behat\Context\Domain\Spread\XlsxFileContext;
use Pim\Behat\Context\Domain\System\PermissionsContext;
use Pim\Behat\Context\Domain\TreeContext;
use Pim\Behat\Context\HookContext;
use Pim\Behat\Context\JobContext;
use Pim\Behat\Context\PimContext;
use Pim\Behat\Context\Storage\FileInfoStorage;
use Pim\Behat\Context\Storage\ProductStorage;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Main feature context
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext extends PimContext implements KernelAwareContext
{
    use SpinCapableTrait;

    /** @var KernelInterface */
    protected $kernel;

    /** @var string[] */
    protected static $errorMessages = [];

    /** @var int */
    protected static $timeout;

    /**
     * @var array
     */
    protected $contexts = [];

    /**
     * Register contexts
     *
     * @param string $mainContextClass
     * @param array $parameters
     */
    public function __construct(string $mainContextClass, array $parameters)
    {
        parent::__construct($mainContextClass);
        $this->setTimeout($parameters);
    }

    /**
     * @param string $context
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSubcontext(string $context)
    {
        if (!isset($this->contexts[$context])) {
            throw new \Exception(sprintf('The context %s does not exist', $context));
        }

        return $this->contexts[$context];
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->contexts['fixtures'] = $environment->getContext(FixturesContext::class);
        $this->contexts['catalogConfiguration'] = $environment->getContext(CatalogConfigurationContext::class);
        $this->contexts['domain-family-variants'] = $environment->getContext(FamilyVariantConfigurationContext::class);
        $this->contexts['webUser'] = $environment->getContext(WebUser::class);
        $this->contexts['datagrid'] = $environment->getContext(DataGridContext::class);
        $this->contexts['command'] = $environment->getContext(CommandContext::class);
        $this->contexts['navigation'] = $environment->getContext(NavigationContext::class);
        $this->contexts['transformations'] = $environment->getContext(TransformationContext::class);
        $this->contexts['assertions'] = $environment->getContext(AssertionContext::class);
        $this->contexts['domain-attribute-tab'] = $environment->getContext(AttributeTabContext::class);
        $this->contexts['domain-completeness'] = $environment->getContext(CompletenessContext::class);
        $this->contexts['domain-export-profiles'] = $environment->getContext(ExportProfilesContext::class);
        $this->contexts['domain-xlsx-files'] = $environment->getContext(XlsxFileContext::class);
        $this->contexts['domain-import-profiles'] = $environment->getContext(ImportProfilesContext::class);
        $this->contexts['domain-pagination-grid'] = $environment->getContext(GridPaginationContext::class);
        $this->contexts['domain-tree'] = $environment->getContext(TreeContext::class);
        $this->contexts['domain-secondary-actions'] = $environment->getContext(SecondaryActionsContext::class);
        $this->contexts['domain-group'] = $environment->getContext(ProductGroupContext::class);
        $this->contexts['hook'] = $environment->getContext(HookContext::class);
        $this->contexts['job'] = $environment->getContext(JobContext::class);
        $this->contexts['viewSelector'] = $environment->getContext(ViewSelectorContext::class);
        $this->contexts['storage-product'] = $environment->getContext(ProductStorage::class);
        $this->contexts['storage-file-info'] = $environment->getContext(FileInfoStorage::class);
        $this->contexts['attribute-validation'] = $environment->getContext(AttributeValidationContext::class);
        $this->contexts['role'] = $environment->getContext(PermissionsContext::class);
        $this->contexts['export-builder'] = $environment->getContext(ExportBuilderContext::class);
    }

    /**
     * @return int the timeout in milliseconds
     */
    public static function getTimeout()
    {
        return static::$timeout;
    }


    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns Container instance.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Return doctrine manager instance
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Transform a list to array
     *
     * @param string $list
     *
     * @return array
     */
    public function listToArray($list)
    {
        if (empty($list)) {
            return [];
        }

        return explode(', ', str_replace(' and ', ', ', $list));
    }

    /**
     * Create an expectation exception
     *
     * @param string $message
     *
     * @return ExpectationException
     */
    public function createExpectationException($message)
    {
        return new ExpectationException($message, $this->getSession());
    }

    /**
     * Add an error message
     *
     * @param string $message
     */
    public function addErrorMessage($message)
    {
        self::$errorMessages[] = $message;
    }

    /**
     * Get error messages
     *
     * @return array $messages
     */
    public static function getErrorMessages()
    {
        return self::$errorMessages;
    }

    /**
     * Wait
     *
     * @param string $condition
     *
     * @throws TimerException If timeout is reached
     */
    public function wait($condition = null)
    {
        if (!($this->getSession()->getDriver() instanceof Selenium2Driver)) {
            return;
        }

        $timeout = $this->getTimeout();

        $start = microtime(true);
        $end   = $start + $timeout / 1000.0;

        if ($condition === null) {
            $defaultCondition = true;
            $conditions       = [
                "document.readyState == 'complete'",           // Page is ready
                "typeof $ != 'undefined'",                     // jQuery is loaded
                "!$.active",                                   // No ajax request is active
                "$('#page').css('display') != 'none'",         // Page is displayed (no progress bar)
                // Page is not loading (no black mask loading page)
                "($('.hash-loading-mask .loading-mask').length == 0 || $('.hash-loading-mask .loading-mask').css('display') == 'none')",
                "$('.jstree-loading').length == 0",            // Jstree has finished loading
            ];

            $condition = implode(' && ', $conditions);
        } else {
            $conditions = [];
            $defaultCondition = false;
        }

        // Make sure the AJAX calls are fired up before checking the condition
        $this->getSession()->wait(100, false);

        $this->getSession()->wait($timeout, $condition);

        // Check if we reached the timeout unless the condition is false to explicitly wait the specified time
        if ($condition !== false && microtime(true) > $end) {
            $this->getSubcontext('hook')->collectErrors();

            if ($defaultCondition) {
                foreach ($conditions as $condition) {
                    $result = $this->getSession()->evaluateScript($condition);
                    if (!$result) {
                        throw new TimerException(
                            sprintf(
                                'Timeout of %d reached when checking on "%s"',
                                $timeout,
                                $condition
                            )
                        );
                    }
                }
            } else {
                throw new TimerException(sprintf('Timeout of %d reached when checking on %s', $timeout, $condition));
            }
        }
    }

    /**
     * @param PyStringNode $error
     *
     * @Then /^I should see:$/
     */
    public function iShouldSeeText(PyStringNode $error)
    {
        $this->spin(function () use ($error) {
            $this->assertSession()->pageTextContains((string) $error);

            return true;
        }, sprintf('Unable to find the text "%s" in the page', $error));
    }

    /**
     * Clicks link with specified id|title|alt|text
     * Example: When I follow the link "Log In"
     * Example: And I follow the link "Log In"
     *
     * @When /^(?:|I )follow the link "(?P<link>(?:[^"]|\\")*)"$/
     */
    public function followLink(string $link)
    {
        $link = str_replace('\\"', '"', $link);

        $this->spin(function () use ($link) {
            $this->getSession()->getPage()->clickLink($link);
            return true;
        }, sprintf('Link %s is not present on the page', $link));
    }

    /**
     * @param PyStringNode $error
     *
     * @Then /^I should not see:$/
     */
    public function iShouldNotSeeText(PyStringNode $error)
    {
        $this->assertSession()->pageTextNotContains((string) $error);
    }

    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @param string $field
     * @param string $value
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" on the current page$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" on the current page$/
     */
    public function fillFieldOnCurrentPage($field, $value)
    {
        $this->getMainContext()->getSubcontext('navigation')->getCurrentPage()->fillField($field, $value);
    }

    /**
     *
     *
     * @param string $message
     * @param string $label
     *
     * @throws ExpectationException
     *
     * @When /^I click on "(?P<message>(?:[^"]|\\")*)" footer message of the field "(?P<label>(?:[^"]|\\")*)"$/
     */
    public function clickOnFooterMessageOfField($message, $label)
    {
        $footerMessage = $this->getCurrentPage()->findFieldFooterMessageForField($label, $message);

        if (null !== $footerMessage) {
            $footerMessage->click();

            return;
        }

        throw new ExpectationException(
            sprintf('Cannot find any footer message "%s" for field "%s"', $message, $label),
            $this->getSession()
        );
    }

    /**
     * @When /^I open the completeness dropdown$/
     *
     * @throws TimeoutException
     */
    public function iOpenTheCompletenessDropdown()
    {
        $dropdown = $this->spin(function () {
            return $this->getCurrentPage()->getCompletenessDropdownButton();
        }, 'Cannot find the completeness dropdown button');

        $dropdown->click();
    }

    /**
     * @When /^I click on the missing required attributes overview link$/
     *
     * @throws TimeoutException
     */
    public function iClickOnTheMissingRequiredAttributesOverviewLink()
    {
        $link = $this->spin(function () {
            return $this->getCurrentPage()->getMissingRequiredAttributesOverviewLink();
        }, 'Cannot find the missing required attributes link');

        $link->click();
    }

    /**
     * @When /^I should not see any missing required attribute$/
     *
     * @throws ExpectationException
     */
    public function iShouldNotSeeAnyMissingRequiredAttribute()
    {
        $link = $this->getCurrentPage()->getMissingRequiredAttributesOverviewLink();

        if ($link->isValid()) {
            throw new ExpectationException(
                'No missing required attribute should be seen, but some found',
                $this->getSession()
            );
        }
    }

    /**
     * @When /^I should see the text "(?P<text>(?:[^"]|\\")*)" in the total missing required attributes$/
     *
     * @throws TimeoutException
     * @throws ExpectationException
     */
    public function iShouldSeeTheTextInTotalMissingRequiredAttributes($text)
    {
        $link = $this->spin(function () {
            $link = $this->getCurrentPage()->getMissingRequiredAttributesOverviewLink();

            return $link->isValid() ? $link : false;
        }, 'Cannot find the missing required attributes link');

        if ($link->getText() !== $text) {
            throw new ExpectationException(
                sprintf(
                    'Cannot find text "%s" in the total missing required attributes, text "%s" found instead',
                    $text,
                    $link->getText()
                ),
                $this->getSession()
            );
        }
    }

    /**
     * Execute javascript
     *
     * @param string $script
     *
     * @return bool Success or failure
     */
    public function executeScript($script)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $this->getSession()->executeScript($script);

            return true;
        }

        return false;
    }

    /**
     * Get the mail recorder
     *
     * @return \Pim\Bundle\EnrichBundle\Mailer\MailRecorder
     */
    public function getMailRecorder()
    {
        return $this->getContainer()->get('pim_enrich.mailer.mail_recorder');
    }

    /**
     * {@inheritdoc}
     */
    public function assertNumElements($num, $element)
    {
        $this->spin(function () use ($num, $element) {
            parent::assertNumElements($num, $element);

            return true;
        }, sprintf('Spinning for asserting "%d" num elements', $num));
    }

    /**
     * {@inheritdoc}
     */
    public function assertCheckboxChecked($checkbox)
    {
        $this->spin(function () use ($checkbox) {
            parent::assertCheckboxChecked($checkbox);

            return true;
        }, sprintf('Spinning for asserting checkbox "%d" is checked', $checkbox));
    }

    /**
     * {@inheritdoc}
     */
    public function assertCheckboxNotChecked($checkbox)
    {
        $this->spin(function () use ($checkbox) {
            parent::assertCheckboxNotChecked($checkbox);

            return true;
        }, sprintf('Spinning for asserting checkbox "%d" is not checked', $checkbox));
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageContainsText($text)
    {
        $this->spin(function () use ($text) {
            parent::assertPageContainsText($text);

            return true;
        }, sprintf('Current page does not contains "%s"', $text));
    }

    /**
     * Set the waiting timeout
     *
     * @param $parameters
     */
    protected function setTimeout($parameters)
    {
        static::$timeout = $parameters['timeout'];
    }
}
