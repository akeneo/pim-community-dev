<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Context;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
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

        $element = $this->getListTypeFromConnectionType($listType);

        $list = $this->spin(function () use ($element) {
            return $this->getCurrentPage()->getElement($element);
        }, sprintf('Can not find list for "%s"', $listType));

        $this->spin(function () use ($list, $connection) {
            return $list->find('css', sprintf('[title="%s"]', $connection));
        }, sprintf('Can not find connection "%s" in list "%s"', $connection, $listType));
    }

    /**
     * @Given I have the following connections:
     */
    public function iHaveFollowingConnections(TableNode $connectionsData): void
    {
        $flowTypeMap = [
            'data source' => FlowType::DATA_SOURCE,
            'data destination' => FlowType::DATA_DESTINATION,
            'other' => FlowType::OTHER,
        ];

        $createConnectionHandler = $this->getService(CreateConnectionHandler::class);

        foreach ($connectionsData->getColumnsHash() as $columnsHash) {
            $label = $columnsHash['label'];
            $flowType = $flowTypeMap[strtolower($columnsHash['flow type'])] ?? FlowType::OTHER;

            $createConnectionHandler->handle(new CreateConnectionCommand($label, $label, $flowType, false));
        }
    }

    /**
     * @When I click on the ":connection" connection in the ":listType" list
     */
    public function iClickOnConnectionInTheList(string $connection, string $connectionType): void
    {
        $listType = $this->getListTypeFromConnectionType($connectionType);

        $connectionLink = $this->spin(function () use ($listType, $connection) {
            $connectionList = $this->getCurrentPage()->getElement($listType);
            return $connectionList->find('css', sprintf('[title="%s"]', $connection));
        }, sprintf('Can not find connection link for "%s"', $connection));

        Assert::assertNotNull($connectionLink);

        $connectionLink->click();
    }

    /**
     * @Then I am on the ":connection" connection edit page
     */
    public function iAmConnectionEditPage(string $connection): void
    {
        $expectedUrl = "http://httpd/#/connect/connection-settings/$connection/edit";
        $actualFullUrl = $this->getSession()->getCurrentUrl();

        Assert::assertEquals($expectedUrl, $actualFullUrl);
    }

    /**
     * @When I update the connection label with ":label"
     */
    public function iUpdateConnectionWith(string $label): void
    {
        $editForm = $this->getCurrentPage()->getElement('Edit form');

        $editForm->setLabel($label);

        $editForm->save();
    }

    private function getListTypeFromConnectionType(string $connectionType): string
    {
        $type = strtolower($connectionType);

        $map = [
            'data source' => 'Data source connections list',
            'data destination' => 'Data destination connections list',
            'data other' => 'Other connections list',
            'other' => 'Other connections list',
        ];
        if (!isset($map[$type])) {
            throw new \UnexpectedValueException('The flow type you want to access to does not exist.');
        }

        return $map[$type];
    }
}
