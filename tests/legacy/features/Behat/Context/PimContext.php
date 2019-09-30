<?php

namespace Pim\Behat\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Context\FeatureContext;
use Symfony\Component\HttpKernel\KernelInterface;

class PimContext extends RawMinkContext implements KernelAwareContext
{
    /** @var array */
    protected static $placeholderValues = [];

    /** @var KernelInterface */
    private $kernel;

    /** @var string */
    protected $mainContextClass;

    /** @var FeatureContext */
    protected $mainContext;

    /** @var string */
    private static $kernelRootDir;

    public function __construct(string $mainContextClass)
    {
        $this->mainContextClass = $mainContextClass;
    }

    public static function resetPlaceholderValues()
    {
        self::$placeholderValues = [
            '%tmp%'      => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            //TODO: change that later
            '%fixtures%' => self::$kernelRootDir . '/../tests/legacy/features/Context/fixtures/',
            '%web%'      => self::$kernelRootDir . '/../public/'
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
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->mainContext = $environment->getContext($this->mainContextClass);
    }

    /**
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->kernel->getContainer()->get('test.service_container')->get($id);
    }

    /**
     * @param string $name
     *
     * @return object
     */
    protected function getParameter($name)
    {
        return $this->kernel->getContainer()->get('test.service_container')->getParameter($name);
    }

    /**
     * @return KernelInterface
     */
    protected function getKernel()
    {
        return $this->kernel;
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
     * @return FeatureContext
     */
    public function getMainContext(): FeatureContext
    {
        return $this->mainContext;
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
     * @param string $element
     *
     * @return mixed
     */
    protected function getElementOnCurrentPage(string $element)
    {
        return $this->spin(function () use ($element) {
            return $this->getCurrentPage()->getElement($element);
        }, sprintf('%s is not present on the page', $element));
    }
}
