<?php

namespace Specification\Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Tool\Component\Localization\TranslatableUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Updater\AssociationTypeUpdater;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class AssociationTypeUpdaterSpec extends ObjectBehavior
{
    function let(TranslatableUpdater $translatableUpdater)
    {
        $this->beConstructedWith($translatableUpdater);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationTypeUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_an_association_type()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                AssociationTypeInterface::class
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_association_type(
        $translatableUpdater,
        AssociationTypeInterface $associationType
    ) {
        $values = [
            'code'   => 'mycode',
            'labels' => [
                'fr_FR' => 'Vente croisée',
            ],
        ];

        $associationType->setCode('mycode')->shouldBeCalled();
        $translatableUpdater->update($associationType, $values['labels'])->shouldBeCalled();

        $this->update($associationType, $values, []);
    }

    function it_throws_an_exception_when_code_is_not_a_scalar(AssociationTypeInterface $associationType)
    {
        $data = [
            'code' => [],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::scalarExpected(
                    'code',
                    AssociationTypeUpdater::class,
                    []
                )
            )
            ->during('update', [$associationType, $data, []]);
    }

    function it_throws_an_exception_when_labels_is_not_an_array(AssociationTypeInterface $associationType)
    {
        $data = [
            'labels' => 'foo',
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::arrayExpected(
                    'labels',
                    AssociationTypeUpdater::class,
                    'foo'
                )
            )
            ->during('update', [$associationType, $data, []]);
    }

    function it_throws_an_exception_when_a_value_in_labels_array_is_not_a_scalar(AssociationTypeInterface $associationType)
    {
        $data = [
            'labels' => [
                'fr_FR' => [],
            ],
        ];

        $this
            ->shouldThrow(
                InvalidPropertyTypeException::validArrayStructureExpected(
                    'labels',
                    'one of the "labels" values is not a scalar',
                    AssociationTypeUpdater::class,
                    ['fr_FR' => []]
                )
            )
            ->during('update', [$associationType, $data, []]);
    }

    function it_throws_an_exception_when_trying_to_update_a_non_existent_field(
        AssociationTypeInterface $associationType
    ) {
        $values = ['non_existent_field' => 'field'];

        $this
            ->shouldThrow(
                UnknownPropertyException::unknownProperty(
                    'non_existent_field',
                    new NoSuchPropertyException()
                )
            )
            ->during('update', [$associationType, $values, []]);
    }
}
