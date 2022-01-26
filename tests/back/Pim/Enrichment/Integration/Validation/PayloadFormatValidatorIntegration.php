<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ExternalApi\PayloadFormat;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PayloadFormatValidatorIntegration extends TestCase
{
    private ValidatorInterface $validator;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /** @test */
    public function it_validates_a_valid_payload(): void
    {
        $this->assertNoViolationForPayload([]);
        $this->assertNoViolationForPayload(['foo' => 'bar']);
        $this->assertNoViolationForPayload([
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope' => null, 'data' => 'foo'],
                ],
                'price' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => [['amount' => 100, 'currency' => 'USD']]],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => [['amount' => '100', 'currency' => 'USD']]],
                ],
            ],
        ]);
    }

    /** @test */
    public function it_adds_violations_when_validate_a_wrong_value_format(): void
    {
        $payload = [
            'values' => [
                'a_text' => [
                    ['locale' => null, 'scope' => null, 'foo' => 'bar'],
                ],
            ],
        ];
        $violations = $this->validator->validate($payload, new PayloadFormat());
        self::assertStringContainsString(
            'Property "a_text" expects an array with the key "data". Check the expected format on the API documentation.',
            (string) $violations,
            \sprintf('Violation message is not found, have: %s', $violations)
        );
        self::assertStringContainsString(
            'Property "a_text" does not expect the "foo" field',
            (string) $violations,
            \sprintf('Violation message is not found, have: %s', $violations)
        );

        $payload = [
            'values' => [
                'a_text' => ['locale' => null, 'scope' => null, 'foo' => 'bar'],
            ],
        ];
        $violations = $this->validator->validate($payload, new PayloadFormat());
        self::assertStringContainsString(
            'Property "a_text" expect to be an array of array',
            (string) $violations,
            \sprintf('Violation message is not found, have: %s', $violations)
        );
    }

    /** @test */
    public function it_adds_violations_when_validate_a_wrong_price_value_format(): void
    {
        $payloads = [];
        $payloads[] = [
            'values' => [
                'a_price' => [
                    ['locale' => null, 'scope' => null, 'data' => 100],
                ],
            ],
        ];
        $payloads[] = [
            'values' => [
                'a_price' => [
                    ['locale' => null, 'scope' => null, 'data' => null],
                ],
            ],
        ];
        $payloads[] = [
            'values' => [
                'a_price' => [
                    ['locale' => null, 'scope' => null, 'data' => [['amount' => [], 'currency' => 'USD']]],
                ],
            ],
        ];
        $payloads[] = [
            'values' => [
                'a_price' => [
                    ['locale' => null, 'scope' => null, 'data' => [['amount' => 100, 'currency' => []]]],
                ],
            ],
        ];

        foreach ($payloads as $payload) {
            $violations = $this->validator->validate($payload, new PayloadFormat());
            self::assertStringContainsString(
                'The data format sent for the "a_price" attribute is wrong. Please, fill in one value per amount field.',
                (string) $violations,
                \sprintf('Violation message is not found, have: %s', $violations)
            );
        }
    }

    private function assertNoViolationForPayload(array $payload): void
    {
        $violations = $this->validator->validate($payload, new PayloadFormat());
        self::assertSame(0, $violations->count(), \sprintf('Violations are found: %s', $violations));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->get('validator');
    }
}
