<?php

namespace Context;

use Behat\Behat\Exception\BehaviorException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\Domain\Collect\ImportProfilesContext;
use Pim\Behat\Context\Domain\Enrich\VariantGroupContext;
use Pim\Behat\Context\Domain\Spread\ExportProfilesContext;
use Pim\Behat\Context\HookContext;
use Pim\Behat\Context\JobContext;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Main feature context
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    use SpinCapableTrait;

    /** @var KernelInterface */
    protected $kernel;

    /** @var string[] */
    protected static $errorMessages = [];

    /**
     * Register contexts
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->useContext('fixtures', new FixturesContext());
        $this->useContext('catalogConfiguration', new CatalogConfigurationContext());
        $this->useContext('webUser', new WebUser());
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new DataGridContext());
        $this->useContext('command', new CommandContext());
        $this->useContext('navigation', new NavigationContext($parameters['base_url']));
        $this->useContext('transformations', new TransformationContext());
        $this->useContext('assertions', new AssertionContext());
        $this->useContext('technical', new TechnicalContext());

        $this->useContext('domain-variant-group', new VariantGroupContext());
        $this->useContext('hook', new HookContext($parameters['window_width'], $parameters['window_height']));

        $this->useContext('job', new JobContext());
        $this->useContext('domain-import-profiles', new ImportProfilesContext());
        $this->useContext('domain-export-profiles', new ExportProfilesContext());
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
        return $this->getContainer()->get('pim_catalog.doctrine.smart_manager_registry');
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
     * Wait
     *
     * @param int    $time
     * @param string $condition
     *
     * @throws BehaviorException If timeout is reached
     */
    public function wait($time = 30000, $condition = null)
    {
        if (!($this->getSession()->getDriver() instanceof Selenium2Driver)) {
            return;
        }

        $start = microtime(true);
        $end   = $start + $time / 1000.0;

        if ($condition === null) {
            $defaultCondition = true;
            $conditions       = [
                "document.readyState == 'complete'",           // Page is ready
                "typeof $ != 'undefined'",                     // jQuery is loaded
                "!$.active",                                   // No ajax request is active
                "$('#page').css('display') == 'block'",        // Page is displayed (no progress bar)
                "$('.loading-mask').css('display') == 'none'", // Page is not loading (no black mask loading page)
                "$('.jstree-loading').length == 0",            // Jstree has finished loading
            ];

            $condition = implode(' && ', $conditions);
        } else {
            $conditions = [];
            $defaultCondition = false;
        }

        // Make sure the AJAX calls are fired up before checking the condition
        $this->getSession()->wait(100, false);

        $this->getSession()->wait($time, $condition);

        // Check if we reached the timeout unless the condition is false to explicitly wait the specified time
        if ($condition !== false && microtime(true) > $end) {
            if ($defaultCondition) {
                foreach ($conditions as $condition) {
                    $result = $this->getSession()->evaluateScript($condition);
                    if (!$result) {
                        throw new BehaviorException(
                            sprintf(
                                'Timeout of %d reached when checking on "%s"',
                                $time,
                                $condition
                            )
                        );
                    }
                }
            } else {
                throw new BehaviorException(sprintf('Timeout of %d reached when checking on %s', $time, $condition));
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
        });
    }

    /**
     * {@inheritdoc}
     */
    public function assertNumElements($num, $element)
    {
        $this->spin(function () use ($num, $element) {
            parent::assertNumElements($num, $element);

            return true;
        });
    }
}
