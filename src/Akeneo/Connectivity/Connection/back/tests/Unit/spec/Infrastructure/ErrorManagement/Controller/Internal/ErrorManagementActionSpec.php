<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Controller\Internal\ErrorManagementAction;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ErrorManagementActionSpec extends ObjectBehavior
{
    public function let(GetConnectionBusinessErrorsHandler $getConnectionBusinessErrorsHandler): void
    {
        $this->beConstructedWith($getConnectionBusinessErrorsHandler);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ErrorManagementAction::class);
    }

    public function it_normalizes_business_errors(): void
    {
        $businessError1 = new BusinessError(
            'erp',
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            '{"message": "Error 1"}'
        );

        $this->normalizeBusinessErrors([$businessError1])->shouldReturn([$businessError1->normalize()]);
    }
}
