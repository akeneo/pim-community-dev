<?php

namespace spec\Akeneo\Bundle\StorageUtilsBundle\Doctrine;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\ORMException;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;

/**
 * @require Doctrine\ODM\MongoDB\MongoDBException
 */
class SmartManagerRegistrySpec extends ObjectBehavior
{
    function let(
        ManagerRegistry $ormRegistry,
        ManagerRegistry $odmRegistry,
        ManagerRegistry $customRegistry
    ) {
        $this->addRegistry($ormRegistry);
        $this->addRegistry($odmRegistry);
        $this->addRegistry($customRegistry);
    }

    function it_gets_the_alias_namespace_from_the_proper_registry(
        $ormRegistry
    ) {
        $ormRegistry->getAliasNamespace('foo')->willReturn('\Foo');

        $this->getAliasNamespace('foo')->shouldReturn('\Foo');
    }

    function it_gets_the_alias_namespace_from_the_following_registry_if_the_current_one_throw_an_orm_exception(
        $ormRegistry,
        $odmRegistry
    ) {
        $ormRegistry->getAliasNamespace('foo')->willThrow(new ORMException());
        $odmRegistry->getAliasNamespace('foo')->willReturn('\Foo');

        $this->getAliasNamespace('foo')->shouldReturn('\Foo');
    }

    function it_gets_the_alias_namespace_from_the_following_registry_if_the_current_one_throw_an_odm_exception(
        $ormRegistry,
        $odmRegistry,
        $customRegistry
    ) {
        if (!class_exists('Doctrine\ODM\MongoDB\MongoDBException', false)) {
            throw new SkippingException('Mongo ODM is not installed');
        }
        $ormRegistry->getAliasNamespace('foo')->willThrow(new ORMException());
        $odmRegistry->getAliasNamespace('foo')->willThrow(new MongoDBException());
        $customRegistry->getAliasNamespace('foo')->willReturn('\Foo');

        $this->getAliasNamespace('foo')->shouldReturn('\Foo');
    }

    function its_getAliasNamespace_method_throws_exception_when_no_doctrine_registry_is_able_to_get_alias_namespace(
        $ormRegistry,
        $odmRegistry,
        $customRegistry
    ) {
        if (!class_exists('Doctrine\ODM\MongoDB\MongoDBException', false)) {
            throw new SkippingException('Mongo ODM is not installed');
        }
        $ormRegistry->getAliasNamespace('foo')->willThrow(new ORMException());
        $odmRegistry->getAliasNamespace('foo')->willThrow(new MongoDBException());
        $customRegistry->getAliasNamespace('foo')->willThrow(new ORMException());

        $this
            ->shouldThrow(
                new \LogicException('No registered doctrine registry was able to get the alias namespace "foo"')
            )
            ->duringGetAliasNamespace('foo');
    }

    function it_throws_any_other_exception_thrown_by_the_getAliasNamespace_method(
        $ormRegistry
    ) {
        $e = new \Exception();
        $ormRegistry->getAliasNamespace('foo')->willThrow($e);

        $this
            ->shouldThrow($e)
            ->duringGetAliasNamespace('foo');
    }
}
