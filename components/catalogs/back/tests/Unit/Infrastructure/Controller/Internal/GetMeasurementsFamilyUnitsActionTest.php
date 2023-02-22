<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Unit\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Application\Persistence\Measurement\GetMeasurementsFamilyQueryInterface;
use Akeneo\Catalogs\Infrastructure\Controller\Internal\GetMeasurementsFamilyUnitsAction;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GetMeasurementsFamilyUnitsActionTest extends TestCase
{
    private ?GetMeasurementsFamilyUnitsAction $getMeasurementsFamilyMeasurementsAction;

    protected function setUp(): void
    {
        $getMeasurementsFamilyQuery = $this->createMock(GetMeasurementsFamilyQueryInterface::class);
        $this->getMeasurementsFamilyMeasurementsAction = new GetMeasurementsFamilyUnitsAction(
            $getMeasurementsFamilyQuery,
        );
    }

    public function testItRedirectsIfTheRequestIsNotAnXMLHTTPRequest(): void
    {
        $this->assertInstanceOf(
            RedirectResponse::class,
            ($this->getMeasurementsFamilyMeasurementsAction)(new Request(),
            'code'),
        );
    }

    public function testItAnswersABadRequestIfTheQueryIsInvalid(): void
    {
        $this->expectException(BadRequestHttpException::class);

        ($this->getMeasurementsFamilyMeasurementsAction)(
            new Request(
                query: [
                    'locale' => 42,
                ],
                server: [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ],
            ),
        'code'
        );
    }
}
