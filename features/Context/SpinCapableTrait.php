<?php

namespace Context;

trait SpinCapableTrait
{
    /**
     * @param callable $callable
     * @param int      $wait
     * @param string   $message
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function spin($callable, $wait = 20, $message = 'no message')
    {
        for ($i = 0; $i < $wait; ++$i) {
            try {
                if ($result = $callable($this)) {
                    return $result;
                }
            } catch (\Exception $e) {
                // do nothing
            }

            sleep(1);
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $infos = sprintf('Timeout thrown by %s::%s()', $backtrace[0]['class'], $backtrace[0]['function']);

        if (isset($backtrace[0]['file']) && isset($backtrace[0]['line'])) {
            $infos .= PHP_EOL . sprintf('file %s, line %d', $backtrace[0]['file'], $backtrace[0]['line']);
            $infos .= PHP_EOL . sprintf('message : %s', $message);
        }

        throw new \Exception($infos);
    }
}
