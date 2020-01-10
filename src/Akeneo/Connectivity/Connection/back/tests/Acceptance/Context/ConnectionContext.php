<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Acceptance\Context;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection as WriteConnection;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContext implements Context
{
    private $connectionRepository;
    private $fetchConnectionsHandler;
    private $findAConnectionHandler;
    private $createConnectionHandler;
    private $deleteConnectionHandler;
    private $updateConnectionHandler;
    private $violations;

    public function __construct(
        InMemoryConnectionRepository $connectionRepository,
        FetchConnectionsHandler $fetchConnectionsHandler,
        FindAConnectionHandler $findAConnectionHandler,
        CreateConnectionHandler $createConnectionHandler,
        DeleteConnectionHandler $deleteConnectionHandler,
        UpdateConnectionHandler $updateConnectionHandler
    ) {
        $this->connectionRepository = $connectionRepository;
        $this->fetchConnectionsHandler = $fetchConnectionsHandler;
        $this->findAConnectionHandler = $findAConnectionHandler;
        $this->createConnectionHandler = $createConnectionHandler;
        $this->deleteConnectionHandler = $deleteConnectionHandler;
        $this->updateConnectionHandler = $updateConnectionHandler;
    }

    /**
     * @Given no Connection has been created
     */
    public function noConnectionHasBeenCreated(): void
    {
        Assert::eq($this->connectionRepository->count(), 0);
    }

    /**
     * @Given the :flowType Connection :label has been created
     */
    public function theConnectionHasBeenCreated(string $flowType, string $label): void
    {
        $startCount = $this->connectionRepository->count();

        $command = new CreateConnectionCommand(self::slugify($label), $label, self::defineFlowType($flowType));
        $this->createConnectionHandler->handle($command);

        Assert::eq($this->connectionRepository->count(), $startCount + 1);
    }

    /**
     * @When I display the Connections
     */
    public function iDisplayTheConnections(): void
    {
        $this->fetchConnectionsHandler->query();
    }

    /**
     * @When I create the :flowType Connection :label
     */
    public function iCreateTheConnection(string $flowType, string $label): void
    {
        try {
            $command = new CreateConnectionCommand(self::slugify($label), $label, self::defineFlowType($flowType));
            $this->createConnectionHandler->handle($command);
        } catch (ConstraintViolationListException $violationList) {
            $this->violations = $violationList;
        }
    }

    /**
     * @When I delete the :label Connection
     */
    public function iDeleteTheConnection(string $label): void
    {
        $code = self::slugify($label);

        $command = new DeleteConnectionCommand($code);
        $this->deleteConnectionHandler->handle($command);
    }

    /**
     * @When I modify the Connection :label with:
     */
    public function iChangeTheOfTheConnectionBy(string $label, TableNode $table)
    {
        $code = self::slugify($label);
        $data = $table->getColumnsHash()[0];
        $newLabel = $data['label'] ?? $label;
        if (!isset($data['flow_type']) || empty($data['flow_type'])) {
            throw new \InvalidArgumentException('You need to provide a new flow type to update the Connection.');
        }
        $newFlowType = $data['flow_type'];
        $newImage = $data['image'] ?? null;

        // $data['user_role'];
        // $data['user_group'];

        try {
            $command = new UpdateConnectionCommand(
                $code,
                $newLabel,
                $newFlowType,
                $newImage,
                '1',
                '2'
            );
            $this->updateConnectionHandler->handle($command);
        } catch (ConstraintViolationListException $violationList) {
            $this->violations = $violationList;
        }
    }

    /**
     * @When I find the Connection :label
     */
    public function iFindTheConnection(string $label): void
    {
        $query = new FindAConnectionQuery(self::slugify($label));
        $connection = $this->findAConnectionHandler->handle($query);
        Assert::eq($connection->label(), $label);
    }

    /**
     * @Then the Connection :label should exist
     */
    public function theConnectionShouldExist(string $label): void
    {
        $code = self::slugify($label);

        $connection = $this->connectionRepository->findOneByCode($code);

        Assert::isInstanceOf($connection, WriteConnection::class);
        Assert::eq($code, (string) $connection->code());
    }

    /**
     * @Then the Connection :label should not exist
     */
    public function theConnectionShouldNotExist(string $label): void
    {
        $code = self::slugify($label);

        $connection = $this->connectionRepository->findOneByCode($code);

        Assert::null($connection);
    }

    /**
     * @Then there should be :count Connections
     */
    public function thereShouldBeConnections(int $count): void
    {
        Assert::eq($count, $this->connectionRepository->count());
    }

    /**
     * @Then the Connection :label should have credentials
     */
    public function theConnectionShouldHaveCredentials(string $label): void
    {
        $query = new FindAConnectionQuery(self::slugify($label));
        $connection = $this->findAConnectionHandler->handle($query);
        Assert::eq($label, $connection->label());
        Assert::notNull($connection->clientId());
        Assert::string($connection->secret());
    }

    /**
     * @Then the Connection :label label should be :expectedLabel
     */
    public function theConnectionLabelShouldBe(string $label, string $expectedLabel): void
    {
        $connection = $this->connectionRepository->findOneByCode(self::slugify($label));

        Assert::eq($expectedLabel, (string) $connection->label());
    }

    /**
     * @Then the Connection :label flow type should be :expectedFlowType
     */
    public function theConnectionFlowTypeShouldBe(string $label, string $expectedFlowType): void
    {
        $connection = $this->connectionRepository->findOneByCode(self::slugify($label));

        Assert::eq(self::defineFlowType($expectedFlowType), (string) $connection->flowType());
    }

    /**
     * @Then the Connection :label image should be :expectedImage
     */
    public function theConnectionImageShouldBe(string $label, string $expectedImage): void
    {
        $connection = $this->connectionRepository->findOneByCode(self::slugify($label));

        Assert::eq($expectedImage, (string) $connection->image());
    }

    /**
     * @Then the Connection :label user role id should be :expectedUserRoleId
     */
    public function theConnectionUserRoleIdShouldBe(string $label, string $expectedUserRoleId): void
    {
        $connection = $this->connectionRepository->findOneByCode(self::slugify($label));

        // Assert::eq($expectedUserRoleId, (string) );
    }

    /**
     * @Then the Connection :label should not have an image
     */
    public function theConnectionShouldNotHaveAnImage(string $label): void
    {
        $connection = $this->connectionRepository->findOneByCode(self::slugify($label));

        Assert::null($connection->image());
    }

    /**
     * @Then I should have been warn that the code is unique
     */
    public function iShouldHaveBeenWarnThatTheCodeIsUnique()
    {
        Assert::isInstanceOf($this->violations, ConstraintViolationListException::class);

        foreach ($this->violations->getConstraintViolationList() as $violation) {
            if (
                'code' === $violation->getPropertyPath() &&
                'akeneo_connectivity.connection.connection.constraint.code.must_be_unique' === $violation->getMessage()
            ) {
                return;
            }
        }

        throw new \Exception('No exception about code uniqueness received.');
    }

    /**
     * @Then I should have been warn that the image does not exist
     */
    public function iShouldHaveBeenWarnThatTheImageDoesNotExist()
    {
        Assert::isInstanceOf($this->violations, ConstraintViolationListException::class);

        foreach ($this->violations->getConstraintViolationList() as $violation) {
            if (
                'image' === $violation->getPropertyPath() &&
                'akeneo_connectivity.connection.connection.constraint.image.must_exist' === $violation->getMessage()
            ) {
                return;
            }
        }

        throw new \Exception('No exception about image received.');
    }

    private static function slugify(string $label): string
    {
        return str_replace(' ', '', $label);
    }

    private static function defineFlowType(string $flowType): string
    {
        switch ($flowType) {
            case 'destination':
                return FlowType::DATA_DESTINATION;
                break;
            case 'source':
                return FlowType::DATA_SOURCE;
                break;
            case 'other':
                return FlowType::OTHER;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Incorrect flow type "%s"', $flowType));
        }
    }
}
