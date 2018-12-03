<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Persistence;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Persistence\DoctrineRepository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Certificate;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\EntityId;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\IdentityProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Repository;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Root;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\ServiceProvider;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PDO;
use PhpSpec\ObjectBehavior;

class DoctrineRepositorySpec extends ObjectBehavior
{
    function let(
        Connection $connection
    ) {
        $this->beConstructedWith(
            $connection,
            'akeneo_pim_configuration'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DoctrineRepository::class);
    }

    function it_is_a_configuration_repository()
    {
        $this->shouldImplement(Repository::class);
    }

    function it_saves_a_configuration_object($connection)
    {
        $config = new Root(
            Code::fromString('authentication_sso'),
            new IdentityProvider(
                EntityId::fromString('https://idp.jambon.com'),
                Url::fromString('https://idp.jambon.com/'),
                Certificate::fromString('public_certificate')
            ),
            new ServiceProvider(
                EntityId::fromString('https://sp.jambon.com'),
                Certificate::fromString('public_certificate'),
                Certificate::fromString('private_certificate')
            )
        );

        $connection->executeQuery(
            'INSERT INTO akeneo_pim_configuration (code, values) VALUES(:code, :values) ON DUPLICATE KEY UPDATE values = VALUES(:values)',
            [
                'code'   => 'authentication_sso',
                'values' => '{"identityProvider":{"entityId":"https:\/\/idp.jambon.com","url":"https:\/\/idp.jambon.com\/","publicCertificate":"public_certificate"},"serviceProvider":{"entityId":"https:\/\/sp.jambon.com","publicCertificate":"public_certificate","privateCertificate":"private_certificate"}}',
            ]
        )->shouldBeCalled();

        $this->save($config);
    }

    function it_finds_an_existing_configuration($connection, Statement $statement)
    {
        $connection
            ->prepare('SELECT * FROM akeneo_pim_configuration WHERE code = :code')
            ->shouldBeCalled()
            ->willReturn($statement)
        ;

        $statement->bindValue('code', 'authentication_sso')->shouldBeCalled();
        $statement
            ->fetch(PDO::FETCH_ASSOC)
            ->willReturn(
                [
                    'code'   => 'authentication_sso',
                    'values' => '{"identityProvider":{"entityId":"https:\/\/idp.jambon.com","url":"https:\/\/idp.jambon.com\/","publicCertificate":"public_certificate"},"serviceProvider":{"entityId":"https:\/\/sp.jambon.com","publicCertificate":"public_certificate","privateCertificate":"private_certificate"}}'
                ]
            )
        ;

        $expectedConfig = new Root(
            Code::fromString('authentication_sso'),
            new IdentityProvider(
                EntityId::fromString('https://idp.jambon.com'),
                Url::fromString('https://idp.jambon.com/'),
                Certificate::fromString('public_certificate')
            ),
            new ServiceProvider(
                EntityId::fromString('https://sp.jambon.com'),
                Certificate::fromString('public_certificate'),
                Certificate::fromString('private_certificate')
            )
        );

        $this
            ->find('authentication_sso')
            ->shouldBeLike($expectedConfig)
        ;
    }
}
