<?php

namespace Pim\Behat\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Event\BaseScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Context\FeatureContext;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class HookContext
 */
class HookContext extends PimContext
{
    /** @var string[] */
    protected static $errorMessages = [];

    /** @var int */
    protected $windowWidth;

    /** @var int */
    protected $windowHeight;

    /**
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
        if ('doctrine/mongodb-odm' === $this->getParameter('pim_catalog_product_storage_driver')) {
            $purgers[] = new MongoDBPurger($this->getService('doctrine_mongodb')->getManager());
        }

        $purgers[] = new ORMPurger($this->getService('doctrine')->getManager());

        $purgers[] = new DBALPurger(
            $this->getService('database_connection'),
            [
                'pim_session',
                'acl_entries',
                'acl_object_identity_ancestors',
                'acl_object_identities',
                'acl_security_identities',
                'acl_classes'
            ]
        );

        foreach ($purgers as $purger) {
            $purger->purge();
        }

        $this->resetElasticsearchIndex();
    }

    /**
     * @BeforeScenario
     */
    public function clearAclCache()
    {
        $aclManager = $this->getService('oro_security.acl.manager');
        $aclManager->clearCache();
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
        $messages = FeatureContext::getErrorMessages();
        if (!empty($messages)) {
            echo "\n\033[1;31mAttention!\033[0m\n\n";

            foreach ($messages as $message) {
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
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            try {
                $script = "if (typeof $ != 'undefined') { window.onerror=function (err) { $('body').attr('JSerr', err); } }";

                $this->getMainContext()->executeScript($script);
            } catch (\Exception $e) {
                //
            }
        }
    }

    /**
     * Collect and log JS errors
     *
     * @AfterStep
     */
    public function collectErrors()
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            try {
                $script = "return typeof $ != 'undefined' ? $('body').attr('JSerr') || false : false;";
                $result = $this->getSession()->evaluateScript($script);
                if ($result) {
                    $this->getMainContext()->addErrorMessage("WARNING: Encountered a JS error: '{$result}'");
                }
            } catch (\Exception $e) {
                echo "Unable to retrieve js error\n";
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
            if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
                try {
                    $script = 'sessionStorage.clear(); typeof $ !== "undefined" && $(window).off("beforeunload");';
                    $this->getMainContext()->executeScript($script);
                } catch (\Exception $e) {
                    //
                }
            }
        }

        $this->currentPage = null;
    }

    /**
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    private function getSmartRegistry()
    {
        return $this->getService('akeneo_storage_utils.doctrine.smart_manager_registry');
    }

    /**
     * Resets the elasticsearch index
     */
    private function resetElasticsearchIndex()
    {
        $esClient = $this->getService('akeneo_elasticsearch.client');
        $conf = $this->getService('akeneo_elasticsearch.index_configuration.loader')->load();

        if ($esClient->hasIndex()) {
            $esClient->deleteIndex();
        }

        $esClient->createIndex($conf->buildAggregated());
    }
}
