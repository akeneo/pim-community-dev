<?php

namespace Pim\Behat\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class PimContext extends RawMinkContext implements KernelAwareInterface
{
    /** @var array */
    protected static $placeholderValues = [];

    /** @var KernelInterface */
    private $kernel;

    /** @var string */
    private static $kernelRootDir;

    public static function resetPlaceholderValues()
    {
        self::$placeholderValues = [
            '%tmp%'      => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            //TODO: change that later
            '%fixtures%' => self::$kernelRootDir . '/../features/Context/fixtures/'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        self::$kernelRootDir = $kernel->getRootDir();
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function replacePlaceholders($value)
    {
        return strtr($value, self::$placeholderValues);
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

    /**
     * @param string $name
     *
     * @return object
     */
    protected function getParameter($name)
    {
        return $this->kernel->getContainer()->getParameter($name);
    }

    /**
     * Transform a list to array
     *
     * @param string $list
     *
     * @return array
     */
    protected function listToArray($list)
    {
        if (empty($list)) {
            return [];
        }

        return preg_split('/ *, *| and /', $list);
    }

    /*************************************************************/
    /**** transitional methods that should be deleted ideally ****/
    /*************************************************************/

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

    /**
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    protected function getCurrentPage()
    {
        return $this->getNavigationContext()->getCurrentPage();
    }

    /**
     * @param string $page
     *
     * @return \SensioLabs\Behat\PageObjectExtension\PageObject\Page
     */
    protected function getPage($page)
    {
        return $this->getNavigationContext()->getPage($page);
    }

    /**
     * @param string $condition
     */
    protected function wait($condition = null)
    {
        $this->getMainContext()->wait($condition);
    }

    /**
     * @return string
     */
    protected function getRootDir()
    {
        return $this->kernel->getRootDir();
    }
}
