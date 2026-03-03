<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ExceptionContext implements Context
{
    /** @var \Throwable[] */
    private array $throwables;

    public function __construct()
    {
        $this->throwables = [];
    }

    public function add(\Throwable $throwable): void
    {
        $this->throwables[] = $throwable;
    }

    /**
     * @BeforeScenario
     */
    public function clean(): void
    {
        $this->throwables = [];
    }

    /**
     * @Then there is no exception
     */
    public function thereIsNoException(): void
    {
        $throwableMessages = [];
        foreach ($this->throwables as $throwable) {
            $throwableMessages[] = $throwable->getMessage();
        }

        Assert::count($throwableMessages, 0, \sprintf(
            'Some exceptions were raised: %s',
            \implode(PHP_EOL, $throwableMessages)
        ));
    }

    /**
     * @Then /^there is an exception with message: (?P<message>.*)$/
     */
    public function thereIsAExceptionWithMessage(string $message): void
    {
        $actualThrowableMessages = [];

        foreach ($this->throwables as $throwable) {
            if ($throwable->getMessage() === $message) {
                return;
            }
            $actualThrowableMessages[] = $throwable->getMessage();
        }

        if ([] === $actualThrowableMessages) {
            throw new \RuntimeException(\sprintf(
                'No exception found with message "%s", no exception is raised',
                $message
            ));
        }

        throw new \RuntimeException(\sprintf(
            'No exception found with message "%s", actual messages are: %s',
            $message,
            \implode(PHP_EOL, $actualThrowableMessages)
        ));
    }
}
