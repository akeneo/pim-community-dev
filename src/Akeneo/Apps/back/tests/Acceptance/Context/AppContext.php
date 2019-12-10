<?php

declare(strict_types=1);

namespace Akeneo\Apps\Tests\Acceptance\Context;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Command\DeleteAppCommand;
use Akeneo\Apps\Application\Command\DeleteAppHandler;
use Akeneo\Apps\Application\Command\UpdateAppCommand;
use Akeneo\Apps\Application\Command\UpdateAppHandler;
use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Application\Query\FindAnAppHandler;
use Akeneo\Apps\Application\Query\FindAnAppQuery;
use Akeneo\Apps\Domain\Exception\ConstraintViolationListException;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Model\Write\App as WriteApp;
use Akeneo\Apps\Infrastructure\Persistence\InMemory\Repository\InMemoryAppRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppContext implements Context
{
    private $appRepository;
    private $fetchAppsHandler;
    private $findAnAppHandler;
    private $createAppHandler;
    private $deleteAppHandler;
    private $updateAppHandler;
    private $violations;

    public function __construct(
        InMemoryAppRepository $appRepository,
        FetchAppsHandler $fetchAppsHandler,
        FindAnAppHandler $findAnAppHandler,
        CreateAppHandler $createAppHandler,
        DeleteAppHandler $deleteAppHandler,
        UpdateAppHandler $updateAppHandler
    ) {
        $this->appRepository = $appRepository;
        $this->fetchAppsHandler = $fetchAppsHandler;
        $this->findAnAppHandler = $findAnAppHandler;
        $this->createAppHandler = $createAppHandler;
        $this->deleteAppHandler = $deleteAppHandler;
        $this->updateAppHandler = $updateAppHandler;
    }

    /**
     * @Given no App has been created
     */
    public function noAppHasBeenCreated(): void
    {
        Assert::eq($this->appRepository->count(), 0);
    }

    /**
     * @Given the :flowType App :label has been created
     */
    public function theAppHasBeenCreated(string $flowType, string $label): void
    {
        $startCount = $this->appRepository->count();

        $command = new CreateAppCommand(self::slugify($label), $label, self::defineFlowType($flowType));
        $this->createAppHandler->handle($command);

        Assert::eq($this->appRepository->count(), $startCount+1);
    }

    /**
     * @When I display the Apps
     */
    public function iDisplayTheApps(): void
    {
        $this->fetchAppsHandler->query();
    }

    /**
     * @When I create the :flowType App :label
     */
    public function iCreateTheApp(string $flowType, string $label): void
    {
        try {
            $command = new CreateAppCommand(self::slugify($label), $label, self::defineFlowType($flowType));
            $this->createAppHandler->handle($command);
        } catch (ConstraintViolationListException $violationList) {
            $this->violations = $violationList;
        }
    }

    /**
     * @When I delete the :label App
     */
    public function iDeleteTheApp(string $label): void
    {
        $code = self::slugify($label);

        $command = new DeleteAppCommand($code);
        $this->deleteAppHandler->handle($command);
    }

    /**
     * @When I modify the App :label with:
     */
    public function iChangeTheOfTheAppBy(string $label, TableNode $table)
    {
        $code = self::slugify($label);
        $data = $table->getColumnsHash()[0];
        $newLabel = $data['label'] ?? $label;
        if (!isset($data['flow_type']) || empty($data['flow_type'])) {
            throw new \InvalidArgumentException('You need to provide a new flow type to update the app.');
        }
        $newFlowType = $data['flow_type'];
        $newImage = $data['image'] ?? null;

        try {
            $command = new UpdateAppCommand($code, $newLabel, $newFlowType, $newImage);
            $this->updateAppHandler->handle($command);
        } catch (ConstraintViolationListException $violationList) {
            $this->violations = $violationList;
        }
    }

    /**
     * @When I find the App :label
     */
    public function iFindTheApp(string $label): void
    {
        $query = new FindAnAppQuery(self::slugify($label));
        $app = $this->findAnAppHandler->handle($query);
        Assert::eq($label, $app->label());
    }

    /**
     * @Then the App :label should exist
     */
    public function theAppShouldExist(string $label): void
    {
        $code = self::slugify($label);

        $app = $this->appRepository->findOneByCode($code);

        Assert::isInstanceOf($app, WriteApp::class);
        Assert::eq($code, (string) $app->code());
    }

    /**
     * @Then the App :label should not exist
     */
    public function theAppShouldNotExist(string $label): void
    {
        $code = self::slugify($label);

        $app = $this->appRepository->findOneByCode($code);

        Assert::null($app);
    }

    /**
     * @Then There should be :count Apps
     */
    public function thereShouldBeApps(int $count): void
    {
        Assert::eq($count, $this->appRepository->count());
    }

    /**
     * @Then the App :label should have credentials
     */
    public function theAppShouldHaveCredentials(string $label): void
    {
        $query = new FindAnAppQuery(self::slugify($label));
        $app = $this->findAnAppHandler->handle($query);
        Assert::eq($label, $app->label());
        Assert::notNull($app->clientId());
        Assert::string($app->secret());
    }

    /**
     * @Then the App :label label should be :expectedLabel
     */
    public function theAppLabelShouldBe(string $label, string $expectedLabel): void
    {
        $app = $this->appRepository->findOneByCode(self::slugify($label));

        Assert::eq($expectedLabel, (string) $app->label());
    }

    /**
     * @Then the App :label flow type should be :expectedFlowType
     */
    public function theAppFlowTypeShouldBe(string $label, string $expectedFlowType): void
    {
        $app = $this->appRepository->findOneByCode(self::slugify($label));

        Assert::eq(self::defineFlowType($expectedFlowType), (string) $app->flowType());
    }

    /**
     * @Then the App :label image should be :expectedImage
     */
    public function theAppImageShouldBe(string $label, string $expectedImage): void
    {
        $app = $this->appRepository->findOneByCode(self::slugify($label));

        Assert::eq($expectedImage, (string) $app->image());
    }

    /**
     * @Then the App :label should not have an image
     */
    public function theAppShouldNotHaveAnImage(string $label): void
    {
        $app = $this->appRepository->findOneByCode(self::slugify($label));

        Assert::null($app->image());
    }

    /**
     * @Then I should have been warn that the code is unique
     */
    public function iShouldHaveBeenWarnThatTheCodeIsUnique()
    {
        Assert::isInstanceOf($this->violations, ConstraintViolationListException::class);

        foreach ($this->violations->getConstraintViolationList() as $violation) {
            if ('code' === $violation->getPropertyPath() &&
                'akeneo_apps.app.constraint.code.must_be_unique' === $violation->getMessage()
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
            if ('image' === $violation->getPropertyPath() &&
                'akeneo_apps.app.constraint.image.must_exist' === $violation->getMessage()
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
