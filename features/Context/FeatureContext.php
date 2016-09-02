<?php

namespace Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Testwork\Counter\Exception\TimerException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
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

    private $contexts = [];

    /**
     * Register contexts
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->setTimeout($parameters);
    }

    public function getSubcontext($context)
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

        $this->contexts['fixtures'] = $environment->getContext('Context\FixturesContext');
        $this->contexts['catalogConfiguration'] = $environment->getContext('Context\CatalogConfigurationContext');
        $this->contexts['webUser'] = $environment->getContext('Context\WebUser');
//        $this->contexts['webApi'] = $environment->getContext('WebApiContext');
        $this->contexts['datagrid'] = $environment->getContext('Context\DataGridContext');
        $this->contexts['command'] = $environment->getContext('Context\CommandContext');
        $this->contexts['navigation'] = $environment->getContext('Context\NavigationContext');
        $this->contexts['transformations'] = $environment->getContext('Context\TransformationContext');
        $this->contexts['assertions'] = $environment->getContext('Context\AssertionContext');
        $this->contexts['technical'] = $environment->getContext('Context\TechnicalContext');
        $this->contexts['domain-attribute-tab'] = $environment->getContext('Pim\Behat\Context\Domain\Enrich\AttributeTabContext');
        $this->contexts['domain-completeness'] = $environment->getContext('Pim\Behat\Context\Domain\Enrich\CompletenessContext');
        $this->contexts['domain-export-profiles'] = $environment->getContext('Pim\Behat\Context\Domain\Spread\ExportProfilesContext');
        $this->contexts['domain-xlsx-files'] = $environment->getContext('Pim\Behat\Context\Domain\Spread\XlsxFileContext');
        $this->contexts['domain-import-profiles'] = $environment->getContext('Pim\Behat\Context\Domain\Collect\ImportProfilesContext');
        $this->contexts['domain-pagination-grid'] = $environment->getContext('Pim\Behat\Context\Domain\Enrich\GridPaginationContext');
        $this->contexts['domain-panel'] = $environment->getContext('Pim\Behat\Context\Domain\Enrich\PanelContext');
        $this->contexts['domain-product-association-tab'] = $environment->getContext('Pim\Behat\Context\Domain\Enrich\Product\AssociationTabContext');
        $this->contexts['domain-tree'] = $environment->getContext('Pim\Behat\Context\Domain\TreeContext');
        $this->contexts['domain-variant-group'] = $environment->getContext('Pim\Behat\Context\Domain\Enrich\VariantGroupContext');
        $this->contexts['hook'] = $environment->getContext('Pim\Behat\Context\HookContext');
        $this->contexts['job'] = $environment->getContext('Pim\Behat\Context\JobContext');
        $this->contexts['storage-product'] = $environment->getContext('Pim\Behat\Context\Storage\ProductStorage');
        $this->contexts['storage-file-info'] = $environment->getContext('Pim\Behat\Context\Storage\FileInfoStorage');
        $this->contexts['attribute-validation'] = $environment->getContext('Pim\Behat\Context\AttributeValidationContext');
        $this->contexts['role'] = $environment->getContext('Pim\Behat\Context\Domain\System\PermissionsContext');
        $this->contexts['export-builder'] = $environment->getContext('Pim\Behat\Context\Domain\Spread\ExportBuilderContext');
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
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine_mongodb')->getManager();
    }

    /**
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    public function getSmartRegistry()
    {
        return $this->getContainer()->get('akeneo_storage_utils.doctrine.smart_manager_registry');
    }

    /**
     * @return string
     */
    public function getStorageDriver()
    {
        return $this->getContainer()->getParameter('pim_catalog_product_storage_driver');
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
     * @throws BehaviorException If timeout is reached
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
                "$('#page').css('display') == 'block'",        // Page is displayed (no progress bar)
                // Page is not loading (no black mask loading page)
                "($('.loading-mask').length == 0 || $('.loading-mask').css('display') == 'none')",
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
        $this->assertSession()->pageTextContains((string) $error);
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
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        $this->getMainContext()->getSubcontext('navigation')->getCurrentPage()->fillField($field, $value);
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
    public function clickLink($link)
    {
        $this->spin(function () use ($link) {
            parent::clickLink($link);

            return true;
        }, sprintf('Cannot click on the link "%s"', $link));
    }

    /**
     * {@inheritdoc}
     */
    public function assertNumElements($num, $element)
    {
        $this->spin(function () use ($num, $element) {
            parent::assertNumElements($num, $element);

            return true;
        }, sprintf('Spining for asserting "%d" num elements', $num));
    }

    /**
     * {@inheritdoc}
     */
    public function assertPageContainsText($text)
    {
        $this->spin(function () use ($text) {
            parent::assertPageContainsText($text);

            return true;
        }, "Spining for asserting page contains text $text");
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
