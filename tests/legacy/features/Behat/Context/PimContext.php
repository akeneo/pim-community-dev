<?php

namespace Pim\Behat\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\FeatureContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use SensioLabs\Behat\PageObjectExtension\PageObject\PageObject;
use Symfony\Component\HttpKernel\KernelInterface;

class PimContext extends RawMinkContext
{
    protected static array $placeholderValues = [];
    private KernelInterface $kernel;
    protected string $mainContextClass;
    protected FeatureContext $mainContext;
    private static string $projectDir;

    public function __construct(string $mainContextClass, KernelInterface $kernel)
    {
        $this->mainContextClass = $mainContextClass;
        $this->kernel = $kernel;
        self::$projectDir = $kernel->getProjectDir();
    }

    public static function resetPlaceholderValues()
    {
        self::$placeholderValues = [
            '%tmp%'      => $_ENV['BEHAT_TMPDIR'] ?? '/tmp/pim-behat',
            //TODO: change that later
            '%fixtures%' => self::$projectDir . '/tests/legacy/features/Context/fixtures/',
            '%web%'      => self::$projectDir . '/public/'
        ];
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

    protected function getService(string $id): object
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

    protected function getFixturesContext(): FixturesContext
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    protected function getNavigationContext(): NavigationContext
    {
        return $this->getMainContext()->getSubcontext('navigation');
    }

    public function getMainContext(): FeatureContext
    {
        return $this->mainContext;
    }

    protected function getCurrentPage(): PageObject
    {
        return $this->getNavigationContext()->getCurrentPage();
    }

    protected function getPage(string $page): Page
    {
        return $this->getNavigationContext()->getPage($page);
    }

    protected function wait(?string $condition = null)
    {
        $this->getMainContext()->wait($condition);
    }

    protected function getElementOnCurrentPage(string $element)
    {
        return $this->spin(function () use ($element) {
            return $this->getCurrentPage()->getElement($element);
        }, sprintf('%s is not present on the page', $element));
    }
}
