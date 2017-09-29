<?php

/**
 * App kernel for the integration tests.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppKernelTest extends AppKernel
{
    /**
     * Registers your custom bundles
     *
     * @return array
     */
    protected function registerProjectBundles()
    {
        return [
            new Acme\Bundle\AppBundle\AcmeAppBundle(),
            new Akeneo\Test\IntegrationTestsBundle\IntegrationTestsBundle()
        ];
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . 'var'
            . DIRECTORY_SEPARATOR
            . 'cache'
            . DIRECTORY_SEPARATOR
            . 'test_kernel';
    }

    /**
     * @return string
     */
    public function getLogDir(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'logs';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name =  parent::getName() . '_test';
        }

        return $this->name;
    }
}
