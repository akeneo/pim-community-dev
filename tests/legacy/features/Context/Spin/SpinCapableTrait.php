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
    public function spin($callable, $message)
    {
        $start   = microtime(true);
        $timeout = FeatureContext::getTimeout() / 1000.0;
        $end     = $start + $timeout;

        $logThreshold      = (int) $timeout * 0.8;
        $previousException = null;
        $result            = null;
        $looping           = false;

        do {
            if ($looping) {
                usleep(300000);
            }
            try {
                $result = $callable($this);
            } catch (\Exception $e) {
                $previousException = $e;
            }
            $looping = true;
        } while (
            microtime(true) < $end &&
            (null === $result || false === $result || [] === $result) &&
            !$previousException instanceof TimeoutException
        );

        if ($previousException instanceof SpinException) {
            $message = $previousException->getMessage();
        }

        if (null === $message) {
            $message = (null !== $previousException) ? $previousException->getMessage() : 'no message';
        }

        if (null === $result || false === $result || [] === $result) {
            $infos = sprintf('Spin : timeout of %d excedeed, with message : %s', $timeout, $message);
            throw new TimeoutException($infos, 0, $previousException);
        }

        $elapsed = microtime(true) - $start;
        if ($elapsed >= $logThreshold) {
            printf('[%s] Long spin (%d seconds) with message : %s', date('y-md H:i:s'), $elapsed, $message);
        }

        return $result;
    }
}
