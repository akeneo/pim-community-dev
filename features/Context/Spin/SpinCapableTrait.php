<?php

namespace Context\Spin;

use Context\FeatureContext;

trait SpinCapableTrait
{
    /**
     * This method executes $callable every second. If its return value is evaluated to true, the spinning stops and the
     * value is returned. If the return value is falsy or if $callable throw an exception, the spinning continues until
     * the loop limit is reached, in that case a TimeoutException is thrown.
     * If another spinning method is used inside $callable and throws a TimeoutException the current spinning stops
     * immediately to avoid waiting uselessly.
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
        $start = microtime(true);
        $timeout = FeatureContext::getTimeout();
        $end   = $start + ($timeout / 1000.0);

        do {
            $result = $callable($this);
            sleep(1);
        } while (microtime(true) < $end && !$result);

        if (!$result) {
            $infos = sprintf('Spin : timeout of %d excedeed, with message : %s', $timeout, $message);
            throw new TimeoutException($infos);
        }

        return $result;
    }
}
