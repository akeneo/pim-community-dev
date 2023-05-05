<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContextSpec extends ObjectBehavior
{
    public function let(
        AreCredentialsValidCombinationQueryInterface $areCredentialsValidCombinationQuery,
        SelectConnectionCodeByClientIdQueryInterface $selectConnectionCode,
        ConnectionRepositoryInterface $connectionRepository
    ): void {
        $this->beConstructedWith($areCredentialsValidCombinationQuery, $selectConnectionCode, $connectionRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ConnectionContext::class);
    }

    public function it_returns_connection_when_client_id_is_defined(
        $selectConnectionCode,
        $connectionRepository
    ): void {
        $this->setClientId('client_id');

        $connection = new Connection(
            'magento',
            'magento',
            FlowType::DATA_DESTINATION,
            42,
            10,
            null,
            false
        );

        $selectConnectionCode->execute('client_id')->willReturn('12');
        $connectionRepository->findOneByCode('12')->willReturn($connection);

        $this->getConnection()->shouldReturn($connection);
    }

    public function it_returns_null_when_client_id_is_not_defined():void
    {
        $this->getConnection()->shouldReturn(null);
    }

    public function it_returns_connection_as_not_collectable_when_connection_is_not_auditable(
        $areCredentialsValidCombinationQuery,
        $selectConnectionCode,
        $connectionRepository
    ): void {
        $this->setClientId('client_id');
        $this->setUsername('test');

        $connection = new Connection(
            'magento',
            'magento',
            FlowType::DATA_DESTINATION,
            42,
            10,
            null,
            false
        );

        $areCredentialsValidCombinationQuery->execute('client_id', 'test')->willReturn(true);
        $selectConnectionCode->execute('client_id')->willReturn('12');
        $connectionRepository->findOneByCode('12')->willReturn($connection);

        $this->isCollectable()->shouldReturn(false);
    }

    public function it_returns_connection_as_not_collectable_when_credentials_are_not_valid_combination(
        $areCredentialsValidCombinationQuery,
        $selectConnectionCode,
        $connectionRepository
    ): void {
        $this->setClientId('client_id');
        $this->setUsername('username');

        $connection = new Connection(
            'magento',
            'magento',
            FlowType::DATA_DESTINATION,
            42,
            10,
            null,
            true
        );

        $areCredentialsValidCombinationQuery->execute('client_id', 'username')->willReturn(false);
        $selectConnectionCode->execute('client_id')->willReturn('12');
        $connectionRepository->findOneByCode('12')->willReturn($connection);

        $this->isCollectable()->shouldReturn(false);
    }

    public function it_returns_are_credentials_valid_combination($areCredentialsValidCombinationQuery): void
    {
        $this->setClientId('client_id');
        $this->setUsername('username');

        $areCredentialsValidCombinationQuery->execute('client_id', 'username')->willReturn(true);
        $areCredentialsValidCombinationQuery->execute('client_id', 'username')->shouldBeCalled();

        $this->areCredentialsValidCombination()->shouldReturn(true);
    }

    public function it_throws_an_exception_during_is_collectable(): void
    {
        $this
            ->shouldThrow(\LogicException::class)
            ->during('isCollectable');
    }

    public function it_throws_an_exception_during_are_credantials_valid_combination_(): void
    {
        $this
            ->shouldThrow(\LogicException::class)
            ->during('areCredentialsValidCombination');
    }
}
