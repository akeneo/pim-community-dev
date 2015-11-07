<?php

namespace Context\Spin;

use Context\FeatureContext;

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
        $timeout = FeatureContext::getTimeout();
        $end     = $start + ($timeout / 1000.0);

        $logThreshold = (int) $timeout * 0.8;

        do {
            $result = $callable($this);
            sleep(1);
        } while (microtime(true) < $end && !$result);

        if (!$result) {
            $infos = sprintf('Spin : timeout of %d excedeed, with message : %s', $timeout, $message);
            throw new TimeoutException($infos);
        }

        $elapsed = microtime(true) - $start;
        if ($elapsed >= $logThreshold) {
            printf('[%s] Long spin detected with message : %s', date('y-md H:i:s'), $message);
        }

        return $result;
    }
}
