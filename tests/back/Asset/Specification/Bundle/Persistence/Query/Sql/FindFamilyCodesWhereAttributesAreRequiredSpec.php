<?php

namespace Specification\Akeneo\Asset\Bundle\Persistence\Query\Sql;

use Akeneo\Asset\Bundle\Persistence\Query\Sql\FindFamilyCodesWhereAttributesAreRequired;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FindFamilyCodesWhereAttributesAreRequiredSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindFamilyCodesWhereAttributesAreRequired::class);
    }

    function it_finds_families_codes_where_given_attributes_are_required($connection, Statement $statement)
    {
        $attributeCodes = ['attribute_1', 'attribute_2', 'attribute_3', 'attribute_4'];
        $query = <<< SQL
    SELECT DISTINCT family.code AS family_code
    FROM pim_catalog_family AS family
    LEFT JOIN pim_catalog_attribute_requirement AS requirement ON requirement.family_id = family.id
    LEFT JOIN pim_catalog_attribute AS attribute ON attribute.id = requirement.attribute_id
    WHERE attribute.code IN (:attribute_codes) and requirement.required = 1
SQL;
        $connection
            ->executeQuery(
                $query,
                ['attribute_codes' => $attributeCodes],
                ['attribute_codes' => Connection::PARAM_STR_ARRAY]
            )
            ->shouldBeCalled()
            ->willReturn($statement);
        $statement->fetchAll(\PDO::FETCH_COLUMN)->willReturn(['family_1', 'family_2']);

        $this->find($attributeCodes)->shouldReturn(['family_1', 'family_2']);
    }

    function it_returns_empty_array_if_there_is_no_given_attribute($connection)
    {
        $connection->executeQuery(Argument::cetera())->shouldNotBeCalled();

        $this->find([])->shouldReturn([]);
    }
}
