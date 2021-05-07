<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context;

use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SettingsContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param TableNode $creationData
     *
     * @When I create a connection with the following information:
     */
    public function createConnection(TableNode $creationData): void
    {
        $this->getNavigationContext()->iAmOnThePage('Connections create');
        $creationForm = $this->getCurrentPage()->getElement('Creation form');

        $data = $creationData->getColumnsHash()[0];
        $creationForm->setFlowType($data['flow type']);
        $creationForm->setLabel($data['label']);

        $creationForm->save();
    }

    /**
     * @param string $connection
     * @param string $listType
     *
     * @throws \Context\Spin\TimeoutException
     * @throws \UnexpectedValueException
     *
     * @Then I should see the ":connection" connection in the ":listType" list
     */
    public function iShouldSeeTheConnectionInTheList(string $connection, string $listType)
    {
        $this->getNavigationContext()->iAmOnThePage('Connections index');
        $listType = strtolower($listType);
        $map = [
            'data source' => 'Data source connections list',
            'data destination' => 'Data destination connections list',
            'data other' => 'Other connections list',
            'other' => 'Other connections list',
        ];
        if (!isset($map[$listType])) {
            throw new \UnexpectedValueException('The flow type you want to access to does not exist.');
        }

        $element = $map[$listType];
        $list = $this->spin(function () use ($element) {
            return $this->getCurrentPage()->getElement($element);
        }, sprintf('Can not find list for "%s"', $listType));

        $this->spin(function () use ($list, $connection) {
            return $list->find('css', sprintf('[title="%s"]', $connection));
        }, sprintf('Can not find connection "%s" in list "%s"', $connection, $listType));
    }
}
