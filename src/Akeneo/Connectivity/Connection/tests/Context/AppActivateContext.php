<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context;

use Behat\Behat\Tester\Exception\PendingException;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppActivateContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Given I should see :appName app
     */
    public function iShouldSeeApp(string $appName)
    {
        $page = $this->getCurrentPage();

        $appTitle = $this->spin(function () use ($appName, $page) {
            return $page->find('named', ['content', $appName]);
        }, sprintf('Cannot find the %s app', $appName));

        Assert::assertNotNull($appTitle);
    }

    /**
     * @When I click on :appName activate button
     */
    public function iClickOnActivateButton(string $appName)
    {
        $session = $this->getSession();
        $page = $this->getCurrentPage();

        $titleNode = $page->find('named', ['content', $appName]);

        $cardContainer = $titleNode->getParent()->getParent();

        $link = $cardContainer->find('named', ['content', 'Connect']);
        Assert::assertNotNull($link);

        $link->click();

        Assert::assertCount(2, $this->getSession()->getWindowNames());

        $windows = $session->getWindowNames();
        $session->switchToWindow($windows[1]);
    }

    /**
     * @When I am at the url :url
     */
    public function iAmAtTheUrl(string $url)
    {
        $session = $this->getSession();

        $this->spin(function () use ($session, $url) {
            return $session->getCurrentUrl() === $url;
        }, sprintf('Current url is not %s', $url));
    }
}
