<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Read\BusinessError;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BusinessErrorSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'erp',
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            '{"message": "Error 1"}'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(BusinessError::class);
    }

    public function it_normalizes_the_business_error(): void
    {
        $this->normalize()->shouldReturn([
            'connection_code' => 'erp',
            'date_time' => '2020-01-01T00:00:00+00:00',
            'content' => ['message' => 'Error 1']
        ]);
    }
}
