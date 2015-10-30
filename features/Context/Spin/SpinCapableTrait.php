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
        $lastException = null;

        for ($i = 0; $i < FeatureContext::DEFAULT_TIMEOUT / 1000; ++$i) {
            try {
                if ($result = $callable($this)) {
                    return $result;
                }
            } catch (TimeoutException $e) {
                throw $e;
            } catch (\Exception $e) {
                $lastException = $e;
            }

            sleep(1);
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $infos = sprintf('Timeout thrown by %s::%s()', $backtrace[0]['class'], $backtrace[0]['function']);

        if (isset($backtrace[0]['file']) && isset($backtrace[0]['line'])) {
            $infos .= PHP_EOL . sprintf('file %s, line %d', $backtrace[0]['file'], $backtrace[0]['line']);
            $infos .= PHP_EOL . sprintf('message : %s', $message);
        }

        if (null !== $lastException) {
            $infos .= PHP_EOL . sprintf('last exception : %s', $lastException->getMessage());
            $infos .= PHP_EOL . sprintf('file %s, line %d', $lastException->getFile(), $lastException->getLine());
        }

        throw new TimeoutException($infos);
    }
}
