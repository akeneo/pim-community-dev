<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\NativeQueryBuilder;
use PhpSpec\ObjectBehavior;

class NativeQueryBuilderSpec extends ObjectBehavior
{
    function let(TableNameBuilder $tableNameBuilder)
    {
        $this->beConstructedWith($tableNameBuilder, ['model.product' => 'pim_catalog_product']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NativeQueryBuilder::class);
    }

    function it_create_a_sql_query($tableNameBuilder)
    {
        $tableNameBuilder->getTableName('model.category.class', null)->willReturn('pim_catalog_category');
        $tableNameBuilder->getTableName('model.user.class', 'group')->willReturn('oro_user_access_group');

        $this->createQuery('SELECT * FROM `@model.category@` JOIN `@model.product@` JOIN `@model.user#group@`')
            ->shouldReturn('SELECT * FROM `pim_catalog_category` JOIN `pim_catalog_product` JOIN `oro_user_access_group`');
    }

    function it_finds_the_sql_table_name_from_entity_parameter($tableNameBuilder)
    {
        $tableNameBuilder->getTableName('model.category.class', null)->willReturn('pim_catalog_category');

        $this->getTableName('model.category')->shouldReturn('pim_catalog_category');
        $this->getTableName('model.product')->shouldReturn('pim_catalog_product');
    }

    function it_throw_an_exception_if_it_cannot_find_the_table($tableNameBuilder)
    {
        $tableNameBuilder->getTableName('model.value', null)->willThrow(\Exception::class);

        $this->shouldThrow(\LogicException::class)->during('getTableName', ['model.value']);
    }
}
