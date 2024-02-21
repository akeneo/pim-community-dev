<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombinations;
use PhpSpec\ObjectBehavior;

class WrongCredentialsCombinationsSpec extends ObjectBehavior
{
    public function it_is_a_wrong_credentials_combinations(): void
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType(WrongCredentialsCombinations::class);
    }

    public function it_normalizes_an_empty_collection(): void
    {
        $this->beConstructedWith([]);
        $this->normalize()->shouldReturn([]);
    }

    public function it_normalizes_a_collection_of_combination(): void
    {
        $this->beConstructedWith([
            [
                'connection_code' => 'bynder',
                'users' => [
                    'magento' => '2019-05-15T16:25:00+00:00',
                    'erp' => '2019-10-15T16:25:00+00:00',
                ],
            ],
            [
                'connection_code' => 'magento',
                'users' => [
                    'bynder' => '2020-05-15T16:25:00+00:00',
                    'erp' => '2020-10-15T16:25:00+00:00',
                ],
            ],
        ]);

        $this->normalize()->shouldReturn([
            'bynder' => [
                'code' => 'bynder',
                'users' => [
                    ['username' => 'magento', 'date' => '2019-05-15T16:25:00+00:00'],
                    ['username' => 'erp', 'date' => '2019-10-15T16:25:00+00:00'],
                ],
            ],
            'magento' => [
                'code' => 'magento',
                'users' => [
                    ['username' => 'bynder', 'date' => '2020-05-15T16:25:00+00:00'],
                    ['username' => 'erp', 'date' => '2020-10-15T16:25:00+00:00'],
                ],
            ],
        ]);
    }
}
