<?php

// @codingStandardsIgnoreStart
class TestListener implements \PHPUnit_Framework_TestListener
{
    private $pid = null;
    private $pipes;
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
        $this->storeAScreenshot($test);
    }

    private function storeAScreenshot(\PHPUnit_Framework_Test $test)
    {
        if ($test instanceof \PHPUnit_Extensions_Selenium2TestCase) {

            $className = explode('\\', get_class($test));
            try {
                $file = getcwd() . DIRECTORY_SEPARATOR . $this->directory . DIRECTORY_SEPARATOR . end($className);
                $file .= '__' . $test->getName() . '__ ' . date('Y-m-d\TH-i-s') . '.png';
                file_put_contents($file, $test->currentScreenshot());
            } catch (\Exception $e) {

                $file = getcwd() . DIRECTORY_SEPARATOR . $this->directory . DIRECTORY_SEPARATOR . end($className);
                $file .= '__' . $test->getName() . '__ ' . date('Y-m-d\TH-i-s') . '.txt';
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
        if ($suite instanceof PHPUnit_Extensions_SeleniumTestSuite) {
            $this->runPhantom();
        }
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        if ($suite instanceof PHPUnit_Extensions_SeleniumTestSuite) {
            $this->terminatePhantom();
        }
    }

    private function runPhantom()
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w")  // stdout is a pipe that the child will write to
        );

        if (strtolower(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER) == 'phantomjs') {
            if (PHP_OS == 'WINNT') {
                $path = PHPUNIT_TESTSUITE_BROWSER_PATH_WINNT;
            } else {
                $path = PHPUNIT_TESTSUITE_BROWSER_PATH_LINUX;
            }
            $this->pid  = proc_open(
                "{$path} --webdriver=" . PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT,
                $descriptorspec,
                $this->pipes);
            $this->waitServerRun(5, PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST, PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT);
        }
    }

    private function terminatePhantom()
    {

        if (strtolower(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER) == 'phantomjs') {
            if (is_resource($this->pid)) {
                $status = proc_get_status($this->pid);

                fclose($this->pipes[2]);
                fclose($this->pipes[1]);
                fclose($this->pipes[0]);

                if (PHP_OS == 'WINNT') {
                    $output = array();
                    exec("wmic process where name=\"phantomjs.exe\" call terminate", $output);
                } else {
                    $ppid = $status['pid'];
                    //use ps to get all the children of this process, and kill them
                    $pids = preg_split('/\s+/', `ps -o pid --no-heading --ppid $ppid`);
                    foreach($pids as $pid) {
                        if(is_numeric($pid)) {
                            posix_kill($pid, SIGKILL); //9 is the SIGKILL signal
                        }
                    }
                }

                proc_terminate ($this->pid);
                $this->pid = null;
            }
        }
    }

    private function waitServerRun($timeOut = 5, $url = 'localhost', $port  = '4444')
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
