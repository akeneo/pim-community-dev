<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Validator;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Validator\QuantifiedAssociationsStructureValidator;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Validator\QuantifiedAssociationsStructureValidatorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationsStructureValidatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationsStructureValidator::class);
    }

    public function it_is_a_quantified_associations_structure_validator()
    {
        $this->shouldImplement(QuantifiedAssociationsStructureValidatorInterface::class);
    }

    public function it_throws_when_not_array()
    {
        $field = 'quantified_associations';
        $data = null;

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                $field,
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_accepts_numeric_association_type_codes()
    {
        $field = 'quantified_associations';
        $data = [
            '1234' => [],
        ];

        $this->shouldNotThrow()->during('validate', [$field, $data]);
    }

    public function it_throws_when_association_type_values_is_not_an_array()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => 'foo',
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                '"PACK" should contain an array',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_link_type_is_not_a_string()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                0 => [],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                'entity type in "PACK" should be a string',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_links_is_not_an_array()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => 'foo',
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                '"PACK[products]" should contain an array',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_links_is_not_a_sequential_array()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    'foo' => [],
                ],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                '"PACK[products]" should contain an array',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_link_has_no_identifier_and_no_uuid()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    ['quantity' => 3],
                ],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                'a quantified association should contain one of these keys: "identifier" or "uuid"',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_link_has_no_quantity()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'foo'],
                ],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                'a quantified association should contain the key "quantity"',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_link_identifier_is_not_a_string()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    ['identifier' => 1, 'quantity' => 3],
                ],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                'a quantified association should contain a valid identifier',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_link_uuid_is_not_a_string()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    ['uuid' => 1, 'quantity' => 3],
                ],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                'a quantified association should contain a valid uuid',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_link_uuid_is_not_a_valid_uuid()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    ['uuid' => 'invalid_uuid', 'quantity' => 3],
                ],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                'a quantified association should contain a valid uuid',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_throws_when_quantified_link_quantity_is_not_an_integer()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 'bar'],
                ],
            ],
        ];

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                $field,
                'a quantified association should contain a valid quantity',
                QuantifiedAssociationsStructureValidator::class,
                $data
            )
        )->during(
            'validate',
            [$field, $data]
        );
    }

    public function it_does_no_throws_when_valid()
    {
        $field = 'quantified_associations';
        $data = [
            'PACK' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 3],
                ],
            ],
        ];

        $this->shouldNotThrow()->during(
            'validate',
            [$field, $data]
        );
    }
}
