<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Persistence\Sql;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Persistence\Sql\SqlRepository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\ConfigurationNotFound;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Persistence\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Configuration;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Types\Type;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SqlRepositorySpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SqlRepository::class);
    }

    function it_is_a_configuration_repository()
    {
        $this->shouldImplement(Repository::class);
    }

    function it_saves_a_configuration_object($connection, Statement $statement)
    {
        $config = new Configuration(
            new Code('authentication_sso'),
            new IsEnabled(false),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/signon'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate('public_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate('public_certificate'),
                new Certificate('private_certificate')
            )
        );

        $connection->prepare(Argument::type('string'))
            ->willReturn($statement)
        ;
        $statement->bindValue('code', 'authentication_sso', Type::STRING)->shouldBeCalled();
        $statement->bindValue('values', [
            'isEnabled'        => false,
            'identityProvider' => [
                'entityId'          => 'https://idp.jambon.com',
                'signOnUrl'         => 'https://idp.jambon.com/signon',
                'logoutUrl'         => 'https://idp.jambon.com/logout',
                'publicCertificate' => 'public_certificate',
            ],
            'serviceProvider' => [
                'entityId'           => 'https://sp.jambon.com',
                'publicCertificate'  => 'public_certificate',
                'privateCertificate' => 'private_certificate',
            ],
        ], Type::JSON_ARRAY)->shouldBeCalled();
        $statement->execute()->shouldBeCalled();

        $this->save($config);
    }

    function it_finds_an_existing_configuration($connection, Statement $statement)
    {
        $connection
            ->prepare(Argument::type('string'))
            ->willReturn($statement)
        ;
        $statement->execute(['code' => 'authentication_sso'])->shouldBeCalled();
        $statement
            ->fetch(\PDO::FETCH_ASSOC)
            ->willReturn(
                [
                    'code'   => 'authentication_sso',
                    'values' => '{"isEnabled":true,"identityProvider":{"entityId":"https:\/\/idp.jambon.com","signOnUrl":"https:\/\/idp.jambon.com\/signon","logoutUrl":"https:\/\/idp.jambon.com\/logout","publicCertificate":"public_certificate"},"serviceProvider":{"entityId":"https:\/\/sp.jambon.com","publicCertificate":"public_certificate","privateCertificate":"private_certificate"}}'
                ]
            )
        ;

        $expectedConfig = new Configuration(
            new Code('authentication_sso'),
            new IsEnabled(true),
            new IdentityProvider(
                new EntityId('https://idp.jambon.com'),
                new Url('https://idp.jambon.com/signon'),
                new Url('https://idp.jambon.com/logout'),
                new Certificate('public_certificate')
            ),
            new ServiceProvider(
                new EntityId('https://sp.jambon.com'),
                new Certificate('public_certificate'),
                new Certificate('private_certificate')
            )
        );

        $this
            ->find('authentication_sso')
            ->shouldBeLike($expectedConfig)
        ;
    }

    function it_throws_an_exception_when_no_configuration_is_found($connection, Statement $statement)
    {
        $connection
            ->prepare(Argument::type('string'))
            ->willReturn($statement)
        ;
        $statement->execute(['code' => 'authentication_sso'])->shouldBeCalled();
        $statement
            ->fetch(\PDO::FETCH_ASSOC)
            ->willReturn(false)
        ;

        $this
            ->shouldThrow(new ConfigurationNotFound(
                'authentication_sso',
                'No configuration found for code "authentication_sso".'
            ))
            ->during('find', ['authentication_sso'])
        ;
    }
}
