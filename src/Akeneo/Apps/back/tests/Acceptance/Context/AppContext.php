<?php

declare(strict_types=1);

namespace Akeneo\Apps\Tests\Acceptance\Context;

use Akeneo\Apps\Application\Command\CreateAppCommand;
use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Query\FetchAppsHandler;
use Akeneo\Apps\Domain\Model\Read\App;
use Akeneo\Apps\Domain\Model\ValueObject\AppCode;
use Akeneo\Apps\Domain\Model\ValueObject\AppLabel;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Akeneo\Apps\Infrastructure\Persistence\InMemory\Repository\InMemoryAppRepository;
use Behat\Behat\Context\Context;
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
    private $createAppHandler;

    public function __construct(
        InMemoryAppRepository $appRepository,
        FetchAppsHandler $fetchAppsHandler,
        CreateAppHandler $createAppHandler
    ) {
        $this->appRepository = $appRepository;
        $this->fetchAppsHandler = $fetchAppsHandler;
        $this->createAppHandler = $createAppHandler;
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
     *
     * @param string $flowType
     * @param string $label
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
     *
     * @param string $flowType
     * @param string $label
     */
    public function iCreateTheApp(string $flowType, string $label): void
    {
        $command = new CreateAppCommand(self::slugify($label), $label, self::defineFlowType($flowType));
        $this->createAppHandler->handle($command);
    }

    /**
     * @Then the App :label should exists
     */
    public function theAppShouldExists(string $label): void
    {
        $code = self::slugify($label);

        $app = $this->appRepository->findOneByCode($code);
        Assert::isInstanceOf($app, App::class);
        Assert::eq($code, $app->code());
        Assert::eq($label, $app->label());
    }

    /**
     * @Then There should be :count Apps
     *
     * @param int $count
     */
    public function thereShouldBeApps(int $count): void
    {
        Assert::eq($count, $this->appRepository->count());
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
