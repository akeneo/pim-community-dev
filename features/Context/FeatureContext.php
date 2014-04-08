<?php

namespace Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Parser;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Exception\BehaviorException;
use Behat\Behat\Event\StepEvent;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;

/**
 * Main feature context
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    private $kernel;

    private static $errorMessages = [];

    /**
     * Path of the yaml file containing tables that should be excluded from database purge
     * @var string
     */
    private $excludedTablesFile = 'excluded_tables.yml';

    /**
     * Register contexts
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->useContext('fixtures', new FixturesContext());
        $this->useContext('catalogConfiguration', new CatalogConfigurationContext());
        $this->useContext('webUser', new WebUser($parameters['window_width'], $parameters['window_height']));
        $this->useContext('webApi', new WebApiContext($parameters['base_url']));
        $this->useContext('datagrid', new DataGridContext());
        $this->useContext('command', new CommandContext());
        $this->useContext('navigation', new NavigationContext());
        $this->useContext('transformations', new TransformationContext());
        $this->useContext('assertions', new AssertionContext());
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $excludedTablesFile = __DIR__ . '/' . $this->excludedTablesFile;
        if (file_exists($excludedTablesFile)) {
            $parser = new Parser();
            $excludedTables = $parser->parse(file_get_contents($excludedTablesFile));
            $excludedTables = $excludedTables['excluded_tables'];
        } else {
            $excludedTables = array();
        }

        if ('doctrine/mongodb-odm' === $this->getStorageDriver()) {
            $purgers[] = new MongoDBPurger($this->getDocumentManager());
            $excludedTables[] = 'pim_catalog_product';
            $excludedTables[] = 'pim_catalog_product_value';
            $excludedTables[] = 'pim_catalog_media';
        }

        $purgers[] = new SelectiveORMPurger($this->getEntityManager(), $excludedTables);

        foreach ($purgers as $purger) {
            $purger->purge();
        }
    }

    /**
     * @AfterScenario
     */
    public function closeConnection()
    {
        foreach ($this->getSmartRegistry()->getConnections() as $connection) {
            $connection->close();
        }
    }

    /**
     * Take a screenshot when a step fails
     *
     * @param StepEvent $event
     *
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep(StepEvent $event)
    {
        if ($event->getResult() === StepEvent::FAILED) {
            $driver = $this->getSession()->getDriver();
            if ($driver instanceof Selenium2Driver) {
                $dir = getenv('WORKSPACE');
                $id  = getenv('BUILD_ID');
                if (false !== $dir && false !== $id) {
                    $dir = sprintf('%s/../builds/%s/screenshots', $dir, $id);
                } else {
                    $dir = '/tmp/behat/screenshots';
                }

                $lineNum  = $event->getStep()->getLine();
                $filename = strstr($event->getLogicalParent()->getFile(), 'features/');
                $filename = sprintf('%s.%d.png', str_replace('/', '__', $filename), $lineNum);
                $path     = sprintf('%s/%s', $dir, $filename);

                $fs = new \Symfony\Component\Filesystem\Filesystem();
                $fs->dumpFile($path, $driver->getScreenshot());

                if ($id) {
                    $path = sprintf(
                        'http://ci.akeneo.com/screenshots/%s/%s/screenshots/%s',
                        getenv('JOB_NAME'),
                        $id,
                        $filename
                    );
                }

                self::$errorMessages[] = "Step {$lineNum} failed, screenshot available at {$path}";
            }
        }
    }

    /**
     * Print error messages
     *
     * @AfterFeature
     */
    public static function printErrorMessages()
    {
        foreach (self::$errorMessages as $message) {
            echo $message . "\n";
        }

        self::$errorMessages = [];
    }

    /**
     * Listen to JS errors
     *
     * @BeforeStep
     */
    public function listenToErrors()
    {
        $script = "if (typeof $ != 'undefined') { window.onerror=function (err) { $('body').attr('JSerr', err); } }";

        $this->executeScript($script);
    }

    /**
     * Collect and log JS errors
     *
     * @AfterStep
     */
    public function collectErrors()
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $script = "return typeof $ != 'undefined' ? $('body').attr('JSerr') || false : false;";
            $result = $this->getSession()->evaluateScript($script);
            if ($result) {
                self::$errorMessages[] = "WARNING: Encountered a JS error: '{$result}'";
            }
        }
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
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * Return doctrine manager instance
     *
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return ObjectManager
     */
    public function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine_mongodb')->getManager();
    }

    /**
     * @return Doctrine\Common\Persistence\ManagerRegistry
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
        return $this->getContainer()->getParameter('pim_catalog.storage_driver');
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
            return array();
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
     * Wait
     *
     * @param integer $time
     * @param string  $condition
     *
     * @throws BehaviorException If timeout is reached
     */
    public function wait($time = 10000, $condition = null)
    {
        if (!$this->getSession()->getDriver() instanceof Selenium2Driver) {
            return;
        }

        $start = microtime(true);
        $end = $start + $time / 1000.0;

        if ($condition === null) {
            $defaultCondition = true;
            $conditions = [
                "document.readyState == 'complete'",           // Page is ready
                "typeof $ != 'undefined'",                     // jQuery is loaded
                "!$.active",                                   // No ajax request is active
                "$('#page').css('display') == 'block'",        // Page is displayed (no progress bar)
                "$('.loading-mask').css('display') == 'none'", // Page is not loading (no black mask loading page)
                "$('.jstree-loading').length == 0",            // Jstree has finished loading
            ];

            $condition = implode(' && ', $conditions);
        } else {
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
     * Execute javascript
     *
     * @param string $script
     *
     * @return boolean Success or failure
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
     * @return MailRecorder
     */
    public function getMailRecorder()
    {
        return $this->getContainer()->get('pim_enrich.mailer.mail_recorder');
    }
}
