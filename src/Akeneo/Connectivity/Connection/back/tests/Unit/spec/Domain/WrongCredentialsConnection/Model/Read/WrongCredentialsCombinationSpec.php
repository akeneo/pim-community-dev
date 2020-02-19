<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombination;
use PhpSpec\ObjectBehavior;

class WrongCredentialsCombinationSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('magento');
    }

    public function it_is_a_wrong_credentials_combination(): void
    {
        $this->shouldHaveType(WrongCredentialsCombination::class);
    }

    public function it_provides_empty_array_if_there_is_no_users(): void
    {
        $this->users()->shouldReturn([]);
    }

    public function it_adds_and_provides_users(): void
    {
        $firstDate = new \DateTime('2019-05-15T16:25:00+00:00');
        $this->addUser('bynder', $firstDate);
        $this->users()->shouldReturn(['bynder' => $firstDate]);

        $secondDate = new \DateTime();
        $this->addUser('dadada', $secondDate);
        $this->users()->shouldReturn([
            'bynder' => $firstDate,
            'dadada' => $secondDate,
        ]);
    }

    public function it_adds_and_provides_users_without_duplicating_them(): void
    {
        $firstDate = new \DateTime('2019-05-15T16:25:00+00:00');
        $secondDate = new \DateTime('2020-02-14T12:03:40+00:00');

        $this->addUser('bynder', $firstDate);
        $this->users()->shouldReturn(['bynder' => $firstDate]);

        $this->addUser('bynder', $secondDate);
        $this->users()->shouldReturn(['bynder' => $secondDate]);
    }

    public function it_provides_a_connection_code(): void
    {
        $this->connectionCode()->shouldReturn('magento');
    }

    public function it_normalizes_an_empty_object(): void
    {
        $this->normalize()->shouldReturn([
            'code' => 'magento',
            'users' => []
        ]);
    }

    public function it_normalizes(): void
    {
        $bynderDate = new \DateTime('2019-05-15T16:25:00+00:00');
        $anotherDate = new \DateTime('2020-02-14T12:03:40+00:00');
        $this->addUser('bynder', $bynderDate);
        $this->addUser('dadada', $anotherDate);

        $this->normalize()->shouldReturn([
            'code' => 'magento',
            'users' => [
                [
                    'username' => 'bynder',
                    'date' => '2019-05-15T16:25:00+00:00',
                ],
                [
                    'username' => 'dadada',
                    'date' => '2020-02-14T12:03:40+00:00',
                ]
            ]
        ]);
    }
}
