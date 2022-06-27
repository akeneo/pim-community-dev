<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\tests\Acceptance;

use Akeneo\Tool\Bundle\MeasureBundle\Installer\MeasurementInstaller;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * This class is used for running integration tests testing the SQL implementation of query functions and repositories.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AcceptanceTestCase extends KernelTestCase
{
    protected ?MeasurementInstaller $fixturesLoader = null;
    protected ?Connection $connection = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false, 'environment' => 'test_fake']);
        $this->connection = $this->get('doctrine.dbal.default_connection');
    }

    protected function get(string $service)
    {
        return self::$container->get($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->ensureKernelShutdown();
    }

    protected function assertHasValidationError(
        string $errorMessageExpected,
        string $propertyPathExpected,
        ConstraintViolationListInterface $violationList
    ): void {
        $this->assertNotCount(0, $violationList, 'No violation found');
        $foundViolations = [];
        foreach ($violationList as $violation) {
            $foundViolations[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        $propertyPathFound = array_keys($foundViolations);
        $this->assertArrayHasKey(
            $propertyPathExpected,
            $foundViolations,
            sprintf(
                'No violation found at path "%s", found "%s"',
                $propertyPathExpected,
                implode(',', array_values($propertyPathFound))
            )
        );

        $foundViolationMessages = $foundViolations[$propertyPathExpected];
        $this->assertContains(
            $errorMessageExpected,
            $foundViolationMessages,
            sprintf(
                'Violation with text "%s" not found, found "%s"',
                $errorMessageExpected,
                implode(',', array_values($foundViolationMessages))
            )
        );
    }

    protected function assertNoViolation(ConstraintViolationListInterface $violationList): void
    {
        $propertyPathFound = array_map(fn ($violation) => $violation->getPropertyPath(), iterator_to_array($violationList));

        $this->assertCount(0, $violationList, sprintf('Violation list should be empty, found on following path "%s"', implode(', ', $propertyPathFound)));
    }
}
