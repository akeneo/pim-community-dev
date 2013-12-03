<?php

// @codingStandardsIgnoreStart
class TestListener implements \PHPUnit_Framework_TestListener
{
    // @codingStandardsIgnoreEnd
    private $directory;


    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->storeAScreenshot($test);
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->storeAScreenshot($test);
    }

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        //$this->storeAScreenshot($test);
    }

    private function storeAScreenshot(\PHPUnit_Framework_Test $test)
    {
        if ($test instanceof \PHPUnit_Extensions_Selenium2TestCase) {

            $className = explode('\\', get_class($test));
            try {
                $file = getcwd() . DIRECTORY_SEPARATOR . $this->directory . DIRECTORY_SEPARATOR . end($className);
                $file .= '__'
                    . preg_replace('/[^A-Za-z0-9_\-]/', '_', $test->getName())
                    . '__ ' . date('Y-m-d\TH-i-s') . '.png';
                file_put_contents($file, $test->currentScreenshot());
            } catch (\Exception $e) {
                $file .= '.txt';
                file_put_contents(
                    $file,
                    "Screenshot generation doesn't work." . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString()
                );
            }
        }
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {

    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {

    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {

    }

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $groups = $suite->getGroups();
        if ($suite instanceof PHPUnit_Extensions_SeleniumTestSuite ||
            in_array('selenium', $groups)
        ) {
            $this->setSeleniumCoverageFlag();
            $this->runPhantom();
        }
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {

    }

    private function setSeleniumCoverageFlag()
    {
        //create file in tmp folder
        $fileName = getcwd() . DIRECTORY_SEPARATOR .
            'app' . DIRECTORY_SEPARATOR .
            'logs' . DIRECTORY_SEPARATOR .
            'selenium.coverage';

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        if (defined('PHPUNIT_SELENIUM_COVERAGE')) {
            $file = fopen($fileName, "w");
            fclose($file);
        }
    }

    private function runPhantom()
    {
        if (strtolower(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER) == 'phantomjs') {
            if (!$this->waitServerRun(1)) {
                if (PHP_OS == 'WINNT') {
                    pclose(
                        popen(
                            "start /b " . PHPUNIT_TESTSUITE_BROWSER_PATH_WINNT .
                            " --webdriver=" . PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT,
                            "r"
                        )
                    );
                } else {
                    shell_exec(
                        "nohup " . PHPUNIT_TESTSUITE_BROWSER_PATH_LINUX .
                        " --webdriver=" . PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT .
                        " > /dev/null 2> /dev/null &"
                    );
                }
            }
            $this->waitServerRun(
                5,
                PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST,
                PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT
            );
        }
    }

    private function waitServerRun($timeOut = 5, $url = 'localhost', $port = '4444')
    {
        $running = false;
        $i = 0;
        do {
            $fp = @fsockopen($url, intval($port));
            $i++;
            if ($i >= $timeOut) {
                break;
            }
            sleep(1);
        } while (!$fp);
        if ($fp !== false) {
            fclose($fp);
            $running = true;
        }
        return $running;
    }
}
