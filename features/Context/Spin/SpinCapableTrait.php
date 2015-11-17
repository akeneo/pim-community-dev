<?php

namespace Context\Spin;

use Behat\Mink\Exception\ExpectationException;
use Context\FeatureContext;
use PHPUnit_Framework_ExpectationFailedException;

trait SpinCapableTrait
{
    /**
     * This method executes $callable every second.
     * If its return value is evaluated to true, the spinning stops and the value is returned.
     * If the return value is falsy, the spinning continues until the loop limit is reached,
     * In that case a TimeoutException is thrown.
     *
     * @param callable $callable
     * @param string   $message
     *
     * @throws TimeoutException
     *
     * @return mixed
     */
    public function spin($callable, $message = 'no message')
    {
        $start   = microtime(true);
        $timeout = FeatureContext::getTimeout() / 1000.0;
        $end     = $start + $timeout;

        $logThreshold      = (int) $timeout * 0.8;
        $previousException = null;
        $result            = null;

        do {
            try {
                $result = $callable($this);
                sleep(1);
            } catch (\Exception $e) {
                $previousException = $e;
            }
        } while (
            microtime(true) < $end &&
            !$result &&
            !$previousException instanceof TimeoutException &&
            !$previousException instanceof ExpectationException &&
            // todo : should we be dependant of PHPUnit ?
            !$previousException instanceof PHPUnit_Framework_ExpectationFailedException
        );

        if (!$result) {
            $infos = sprintf('Spin : timeout of %d sec excedeed, with message : %s', $timeout, $message);
            throw new TimeoutException($infos, 0, $previousException);
        }

        $elapsed = microtime(true) - $start;
        if ($elapsed >= $logThreshold) {
            printf("[%s] Long spin detected with message : %s\n", date('y-md H:i:s'), $message);
        }

        return $result;
    }
}
