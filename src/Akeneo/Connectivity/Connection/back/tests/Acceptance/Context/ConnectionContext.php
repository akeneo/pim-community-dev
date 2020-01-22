<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Acceptance\Context;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\DeleteConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Exception\ConstraintViolationListException;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection as WriteConnection;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryConnectionRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\InMemory\Repository\InMemoryUserPermissionsRepository;
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
    private $regenerateConnectionSecretHandler;
    private $regenerateConnectionPasswordHandler;
    private $userPermissionsRepository;

    // Stateful properties
    private $violations;
    private $oldSecret;
    private $oldPassword;

    public function __construct(
        InMemoryConnectionRepository $connectionRepository,
        FetchConnectionsHandler $fetchConnectionsHandler,
        FindAConnectionHandler $findAConnectionHandler,
        CreateConnectionHandler $createConnectionHandler,
        DeleteConnectionHandler $deleteConnectionHandler,
        UpdateConnectionHandler $updateConnectionHandler,
        RegenerateConnectionSecretHandler $regenerateConnectionSecretHandler,
        RegenerateConnectionPasswordHandler $regenerateConnectionPasswordHandler,
        InMemoryUserPermissionsRepository $userPermissionsRepository
    ) {
        $this->connectionRepository = $connectionRepository;
        $this->fetchConnectionsHandler = $fetchConnectionsHandler;
        $this->findAConnectionHandler = $findAConnectionHandler;
        $this->createConnectionHandler = $createConnectionHandler;
        $this->deleteConnectionHandler = $deleteConnectionHandler;
        $this->updateConnectionHandler = $updateConnectionHandler;
        $this->regenerateConnectionSecretHandler = $regenerateConnectionSecretHandler;
        $this->regenerateConnectionPasswordHandler = $regenerateConnectionPasswordHandler;
        $this->userPermissionsRepository = $userPermissionsRepository;
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
        if ($label === '<100chars>') {
            $label = str_pad('A', 120, 'a');
        }
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
    public function iModifyTheConnectionWith(string $label, TableNode $table)
    {
        $code = self::slugify($label);

        $data = $table->getColumnsHash()[0];

        $newLabel = $data['label'] ?? $label;
        if ($newLabel === '<100chars>') {
            $newLabel = str_pad('A', 120, 'a');
        }
        if (!isset($data['flow_type']) || empty($data['flow_type'])) {
            throw new \InvalidArgumentException('You need to provide a new flow type to update the Connection.');
        }
        $newFlowType = $data['flow_type'];
        $newImage = $data['image'] ?? null;
        $newRole = $this->userPermissionsRepository->getRoleIdByIdentifier($data['user_role']);
        $newGroup = $this->userPermissionsRepository->getGroupIdByIdentifier($data['user_group']);

        try {
            $command = new UpdateConnectionCommand(
                $code,
                $newLabel,
                $newFlowType,
                $newImage,
                (string) $newRole,
                (string) $newGroup
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
     * @When I regenerate the :label Connection secret
     */
    public function iRegenerateTheConnectionSecret(string $label): void
    {
        $code = self::slugify($label);

        if (!isset($this->connectionRepository->dataRows[$code])) {
            throw new \InvalidArgumentException(sprintf('Connection "%s" does not exist!', $code));
        }
        $this->oldSecret = $this->connectionRepository->dataRows[$code]['secret'];

        $command = new RegenerateConnectionSecretCommand($code);
        $this->regenerateConnectionSecretHandler->handle($command);
    }

    /**
     * @When I regenerate the :label Connection password
     */
    public function iRegenerateTheConnectionPassword(string $label): void
    {
        $code = self::slugify($label);

        if (!isset($this->connectionRepository->dataRows[$code])) {
            throw new \InvalidArgumentException(sprintf('Connection "%s" does not exist!', $code));
        }
        $this->oldPassword = $this->connectionRepository->dataRows[$code]['password'];

        $command = new RegenerateConnectionPasswordCommand($code);
        $this->regenerateConnectionPasswordHandler->handle($command);
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
     * @Then the Connection :label user role should be :expectedUserRole
     */
    public function theConnectionUserRoleIdShouldBe(string $label, string $expectedUserRole): void
    {
        $connection = $this->connectionRepository->findOneByCode(self::slugify($label));

        $role = $this->userPermissionsRepository->getUserRole($connection->userId()->id());

        Assert::eq($expectedUserRole, $role);
    }

    /**
     * @Then the Connection :label user group should be :expectedUserGroup
     */
    public function theConnectionUserGroupIdShouldBe(string $label, string $expectedUserGroup): void
    {
        $connection = $this->connectionRepository->findOneByCode(self::slugify($label));

        $group = $this->userPermissionsRepository->getUserGroup($connection->userId()->id());

        Assert::eq($expectedUserGroup, $group);
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
        if (!$this->assertConstraintViolation('code', 'akeneo_connectivity.connection.connection.constraint.code.must_be_unique')) {
            throw new \Exception('No violation about code uniqueness received.');
        }
    }

    /**
     * @Then I should have been warn the :field should be longer than 3 chars
     */
    public function iShouldHaveBeenWarnTheShouldBeLongerThan3Chars(string $field)
    {
        if (!$this->assertConstraintViolation($field, 'akeneo_connectivity.connection.connection.constraint.'.$field.'.too_short')) {
            throw new \Exception(sprintf('No violation about %s length received.', $field));
        }
    }

    /**
     * @Then I should have been warn the :field should be smaller than 100 chars
     */
    public function iShouldHaveBeenWarnTheShouldBeSmallerThan100Chars(string $field): void
    {
        if (!$this->assertConstraintViolation($field, 'akeneo_connectivity.connection.connection.constraint.'.$field.'.too_long')) {
            throw new \Exception(sprintf('No violation about %s length received.', $field));
        }
    }

    /**
     * @Then I should have been warn the :field should not be empty
     */
    public function iShouldHaveBeenWarnTheShouldNotBeEmpty(string $field)
    {
        if (!$this->assertConstraintViolation($field, 'akeneo_connectivity.connection.connection.constraint.'.$field.'.required')) {
            throw new \Exception(sprintf('No violation about empty %s received.', $field));
        }
    }

    /**
     * @Then I should have been warn the code is invalid
     */
    public function iShouldHaveBeenWarnTheCodeIsInvalid()
    {
        if (!$this->assertConstraintViolation('code', 'akeneo_connectivity.connection.connection.constraint.code.invalid')) {
            throw new \Exception('No violation about invalid code received.');
        }
    }

    /**
     * @Then I should have been warn the flow type is invalid
     */
    public function iShouldHaveBeenWarnTheFlowTypeIsInvalid()
    {
        if (!$this->assertConstraintViolation('flowType', 'akeneo_connectivity.connection.connection.constraint.flow_type.invalid')) {
            throw new \Exception('No violation about invalid flow type received.');
        }
    }

    /**
     * @Then the :label Connection secret should have been changed
     */
    public function theConnectionSecretShouldHaveBeenChanged(string $label): void
    {
        $code = self::slugify($label);

        if (!isset($this->connectionRepository->dataRows[$code])) {
            throw new \InvalidArgumentException(sprintf('Connection "%s" does not exist!', $code));
        }
        $newSecret = $this->connectionRepository->dataRows[$code]['secret'];

        Assert::notEq($this->oldSecret, $newSecret);
    }

    /**
     * @Then the :label Connection password should have been changed
     */
    public function theConnectionPasswordShouldHaveBeenChanged(string $label): void
    {
        $code = self::slugify($label);

        if (!isset($this->connectionRepository->dataRows[$code])) {
            throw new \InvalidArgumentException(sprintf('Connection "%s" does not exist!', $code));
        }
        $newPassword = $this->connectionRepository->dataRows[$code]['password'];

        Assert::notEq($this->oldPassword, $newPassword);
    }

    private function assertConstraintViolation(string $propertyPath, string $message): bool
    {
        Assert::isInstanceOf($this->violations, ConstraintViolationListException::class);

        foreach ($this->violations->getConstraintViolationList() as $violation) {
            if ($propertyPath === $violation->getPropertyPath() && $message === $violation->getMessage()) {
                return true;
            }
        }

        return false;
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
                return $flowType;
        }
    }
}
