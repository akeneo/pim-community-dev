<?php

namespace Context\Spin;

trait SpinCapableTrait
{
    /**
     * This method executes $callable every second.
     * If its return value is evaluated to true, the spinning stops and the value is returned.
     * If the return value is falsy, the spinning continues until the loop limit is reached,
     * In that case a TimeoutException is thrown.
     *
     * @param callable $callable
     * @param int      $timeout
     * @param string   $message
     *
     * @return mixed
     * @throws TimeoutException
     */
    public function spin($callable, $timeout = 20, $message = 'no message')
    {
        $start = microtime(true);
        $end   = $start + $timeout;

        $logThreshold      = (int) $timeout * 0.8;
        $previousException = null;
        $result            = null;
        $looping           = false;

        do {
            if ($looping) {
                sleep(1);
            }
            try {
                $result = $callable($this);
            } catch (\Exception $e) {
                $previousException = $e;
            }
            $looping = true;
        } while (
            microtime(true) < $end &&
            !$result &&
            !$previousException instanceof TimeoutException
        );

        if (!$result) {
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
