<?php

namespace Pim\Behat\Context;

use Akeneo\Tool\Bundle\BatchQueueBundle\Command\JobQueueConsumerCommand;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Testwork\Tester\Result\TestResult;
use Context\FeatureContext;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use WebDriver\Exception\UnexpectedAlertOpen;

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

    /** @var Process */
    protected $jobConsumerProcess;

    /**
     * @param string $mainContextClass
     * @param int $windowWidth
     * @param int $windowHeight
     */
    public function __construct(string $mainContextClass, int $windowWidth, int $windowHeight)
    {
        parent::__construct($mainContextClass);
        $this->windowWidth  = $windowWidth;
        $this->windowHeight = $windowHeight;
    }

    /**
     * @BeforeScenario
     */
    public function launchJobConsumer()
    {
        $process = new Process(sprintf('exec bin/console %s --env=behat', JobQueueConsumerCommand::COMMAND_NAME));
        $process->setTimeout(null);
        $process->start();

        $this->jobConsumerProcess = $process;
    }

    /**
     * @AfterScenario
     */
    public function stopJobConsumer()
    {
        $this->jobConsumerProcess->stop();
    }

    /**
     * @AfterScenario
     */
    public function closeConnection()
    {
        foreach ($this->getDoctrine()->getConnections() as $connection) {
            $connection->close();
        }
    }

    /**
     * Take a screenshot when a step fails
     *
     * @param AfterStepScope $event
     *
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep(AfterStepScope $event)
    {
        if ($event->getTestResult()->getResultCode() === TestResult::FAILED) {
            $driver = $this->getSession()->getDriver();

            if ($driver instanceof Selenium2Driver) {
                $dir = !empty(getenv('BEHAT_SCREENSHOT_PATH')) ? getenv('BEHAT_SCREENSHOT_PATH') : '/tmp/behat/screenshots';

                $lineNum  = $event->getStep()->getLine();
                $filename = strstr($event->getFeature()->getFile(), 'features/');
                $filename = sprintf('%s.%d.png', str_replace('/', '__', $filename), $lineNum);
                $path     = sprintf('%s/%s', $dir, $filename);

                $fs = new \Symfony\Component\Filesystem\Filesystem();
                $fs->dumpFile($path, $driver->getScreenshot());

                $this->getMainContext()->addErrorMessage("Step {$lineNum} failed, screenshot available at {$path}");
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
            $script = "return typeof $ != 'undefined' ? $('body').attr('JSerr') || false : false;";

            // This check won't work with steps provoking an alert to open, in this case skip it.
            try {
                $result = $this->getSession()->evaluateScript($script);
            } catch (UnexpectedAlertOpen $e) {
                return;
            }

            if ($result) {
                $this->getMainContext()->addErrorMessage("WARNING: Encountered a JS error: '{$result}'");

                throw new JSErrorEncounteredException("Encountered a JS error: '{$result}'");
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
        //TODO
//        $this->getMainContext()->getMailRecorder()->clear();
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
        foreach ($this->getDoctrine()->getEntityManagers() as $manager) {
            $manager->clear();
        }
    }

    /**
     * @param AfterScenarioScope $event
     *
     * @AfterScenario
     */
    public function resetCurrentPage(AfterScenarioScope $event)
    {
        if ($event->getTestResult() !== StepResult::UNDEFINED) {
            if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
                try {
                    $script = 'sessionStorage.clear(); localStorage.clear(); typeof $ !== "undefined" && $(window).off("beforeunload");';
                    $this->getMainContext()->executeScript($script);
                } catch (\Exception $e) {
                    //
                }
            }
        }

        $this->currentPage = null;
    }

    /**
     * @return RegistryInterface
     */
    private function getDoctrine()
    {
        return $this->getService('doctrine');
    }

    /**
     * Resets the elasticsearch index
     */
    private function resetElasticsearchIndex()
    {
        $clientRegistry = $this->getService('akeneo_elasticsearch.registry.clients');
        $clients = $clientRegistry->getClients();

        foreach ($clients as $client) {
            $client->resetIndex();
        }
    }
}
