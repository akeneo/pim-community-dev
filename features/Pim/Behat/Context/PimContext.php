<?php

namespace Pim\Behat\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class PimContext extends RawMinkContext implements KernelAwareInterface
{
    /** @var array */
    protected $placeholderValues = [];

    /** @var KernelInterface */
    private $kernel;

    public function __construct()
    {
        $this->resetPlaceholderValues();
    }

    /**
     * @BeforeScenario
     */
    public function resetPlaceholderValues()
    {
        $this->placeholderValues = [
            '%tmp%'      => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            '%fixtures%' => __DIR__ . '/fixtures'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function replacePlaceholders($value)
    {
        return strtr($value, $this->placeholderValues);
    }

    /**
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->kernel->getContainer()->get($id);
    }





    /***************************************************/
    /**** temporary methods that should be deleted ****/
    /***************************************************/

    /**
     * @return \Context\FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * @return \Context\NavigationContext
     */
    protected function getNavigationContext()
    {
        return $this->getMainContext()->getSubcontext('navigation');
    }
}
