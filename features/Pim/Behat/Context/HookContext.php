<?php

namespace Pim\Behat\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Event\BaseScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Context\FeatureContext;
use Context\SelectiveORMPurger;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

/**
 * Class HookContext
 */
class HookContext extends PimContext
{
    /** @var string[] */
    protected static $errorMessages = [];

    /**
     * Path of the yaml file containing tables that should be excluded from database purge
     *
     * @var string
     */
    protected $excludedTablesFile = 'excluded_tables.yml';

    /** @var int */
    protected $windowWidth;

    /** @var int */
    protected $windowHeight;

    /**
     * Constructor
     *
     * @param int $windowWidth
     * @param int $windowHeight
     */
    public function __construct($windowWidth, $windowHeight)
    {
        $this->windowWidth  = $windowWidth;
        $this->windowHeight = $windowHeight;
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        $excludedTablesFile = __DIR__ . '/' . $this->excludedTablesFile;
        if (file_exists($excludedTablesFile)) {
            $parser         = new Parser();
            $excludedTables = $parser->parse(file_get_contents($excludedTablesFile));
            $excludedTables = $excludedTables['excluded_tables'];
        } else {
            $excludedTables = [];
        }

        if ('doctrine/mongodb-odm' === $this->getParameter('pim_catalog_product_storage_driver')) {
            $purgers[]        = new MongoDBPurger($this->getService('doctrine_mongodb')->getManager());
            $excludedTables[] = 'pim_catalog_product';
            $excludedTables[] = 'pim_catalog_product_value';
            $excludedTables[] = 'pim_catalog_media';
        }

        $purgers[] = new SelectiveORMPurger($this->getService('doctrine')->getManager(), $excludedTables);

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

            $rootDir   = dirname($this->getParameter('kernel.root_dir'));
            $filePath  = $event->getLogicalParent()->getFile();
            $stepStats = [
                'scenario_file'  => substr($filePath, strlen($rootDir) + 1),
                'scenario_line'  => $event->getLogicalParent()->getLine(),
                'scenario_label' => $event->getLogicalParent()->getTitle(),
                'exception'      => $event->getException()->getMessage(),
                'step_line'      => $event->getStep()->getLine(),
                'step_label'     => $event->getStep()->getText(),
                'status'         => 'failed'
            ];

            if ($driver instanceof Selenium2Driver) {
                $dir      = getenv('WORKSPACE');
                $buildUrl = getenv('BUILD_URL');
                if (false !== $dir) {
                    $dir = sprintf('%s/app/build/screenshots', $dir);
                } else {
                    $dir = '/tmp/behat/screenshots';
                }

                $lineNum  = $event->getStep()->getLine();
                $filename = strstr($event->getLogicalParent()->getFile(), 'features/');
                $filename = sprintf('%s.%d.png', str_replace('/', '__', $filename), $lineNum);
                $path     = sprintf('%s/%s', $dir, $filename);

                $fs = new \Symfony\Component\Filesystem\Filesystem();
                $fs->dumpFile($path, $driver->getScreenshot());

                if (false !== $dir) {
                    $path = sprintf(
                        '%s/artifact/app/build/screenshots/%s',
                        $buildUrl,
                        $filename
                    );
                }

                $stepStats['screenshot'] = $path;
                $this->getMainContext()->addErrorMessage("Step {$lineNum} failed, screenshot available at {$path}");
            }

            if ('JENKINS' === getenv('BEHAT_CONTEXT')) {
                echo sprintf("\033[1;37m##glados_step##%s##glados_step##\033[0m\n", json_encode($stepStats));
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
        if (!empty(FeatureContext::$errorMessages)) {
            echo "\n\033[1;31mAttention!\033[0m\n\n";

            foreach (FeatureContext::$errorMessages as $message) {
                echo $message . "\n";
            }

            self::$errorMessages = [];
        }
    }

    /**
     * Listen to JS errors
     *
     * @BeforeStep
     */
    public function listenToErrors()
    {
        $script = "if (typeof $ != 'undefined') { window.onerror=function (err) { $('body').attr('JSerr', err); } }";

        $this->getMainContext()->executeScript($script);
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
                $this->getMainContext()->addErrorMessage("WARNING: Encountered a JS error: '{$result}'");
            }
        }
    }

    /**
     * @BeforeScenario
     */
    public function maximize()
    {
        try {
            $this->getSession()->resizeWindow($this->windowWidth, $this->windowHeight);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @BeforeScenario
     */
    public function clearRecordedMails()
    {
        $this->getMainContext()->getMailRecorder()->clear();
    }

    /**
     * @BeforeScenario
     */
    public static function resetPlaceholderValues()
    {
        parent::resetPlaceholderValues();
    }

    /**
     * @BeforeScenario
     */
    public function removeTmpDir()
    {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove(self::$placeholderValues['%tmp%']);
    }

    /**
     * @BeforeScenario
     */
    public function clearUOW()
    {
        foreach ($this->getSmartRegistry()->getManagers() as $manager) {
            $manager->clear();
        }
    }

    /**
     * @BeforeScenario
     */
    public function clearPimFilesystem()
    {
        foreach ($this->getPimFilesystems() as $fs) {
            foreach ($fs->listContents() as $key) {
                if ('dir' === $key['type']) {
                    $fs->deleteDir($key['path']);
                } else {
                    $fs->delete($key['path']);
                }
            }
        }
    }

    /**
     * @return Filesystem[]
     */
    protected function getPimFilesystems()
    {
        return [];
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
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    private function getSmartRegistry()
    {
        return $this->getService('pim_catalog.doctrine.smart_manager_registry');
    }
}
