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

        $infos = sprintf('Timeout thrown by %s::%s()', $backtrace[1]['class'], $backtrace[1]['function']);

        if (isset($backtrace[1]['file']) && isset($backtrace[1]['line'])) {
            $infos .= PHP_EOL . sprintf('file %s, line %d', $backtrace[1]['file'], $backtrace[1]['line']);
            $infos .= PHP_EOL . sprintf('message : %s', $message);
        }

        throw new \Exception($infos);
    }
}
