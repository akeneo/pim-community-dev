<?php

namespace Oro\Bundle\FormBundle\Tests\Functional;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    /**
     * @var KernelInterface
     */
    protected static $kernel;

    /**
     * @return KernelInterface
     */
    public function getKernel()
    {
        return self::$kernel;
    }

    /**
     * @param array $options
     * @return KernelInterface
     */
    protected static function createKernel(array $options = array())
    {
        $kernel = new AppKernel(
            isset($options['config']) ? $options['config'] : 'config.yml'
        );

        return $kernel;
    }
}
