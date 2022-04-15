<?php


namespace Specification\Akeneo\Channel\Infrastructure\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SqlFindLocalesSpec extends ObjectBehavior
{
    public function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    public function it_finds_a_locale_by_its_code_and_caches_it(
        Connection $connection,
        Result $result
    ) {
        $connection
            ->executeQuery(Argument::any(), Argument::any())
            ->willReturn($result)
            ->shouldBeCalledOnce()
        ;

        $result->fetchAssociative()->willReturn([
            'localeCode' => 'en_US',
            'isActivated' => true,
        ]);

        $this->find('en_US');
        $this->find('en_US');
        $this->find('en_US');
    }

    public function it_finds_all_activated_locales_and_caches_them(
        Connection $connection,
        Result $result
    ) {
        $connection
            ->executeQuery(Argument::any())
            ->willReturn($result)
            ->shouldBeCalledOnce()
        ;

        $result->fetchAllAssociative()->willReturn(
            [
                [
                    'localeCode' => 'en_US',
                    'isActivated' => true,
                ],
                [
                    'localeCode' => 'fr_FR',
                    'isActivated' => true,
                ],
            ]
        );

        $this->findAllActivated();
        $this->findAllActivated();
        $this->findAllActivated();
    }
}
