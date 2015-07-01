<?php

namespace spec\Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationTypeTranslation;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;

class AssociationTypeUpdaterSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $associationTypeRepository)
    {
        $this->beConstructedWith($associationTypeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Updater\AssociationTypeUpdater');
    }

    function it_is_a_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
    }

    function it_throws_an_exception_when_trying_to_update_anything_else_than_an_association_type()
    {
        $this->shouldThrow(
            new \InvalidArgumentException(
                'Expects a "Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface", "stdClass" provided.'
            )
        )->during(
            'update',
            [new \stdClass(), []]
        );
    }

    function it_updates_a_association_type(
        AssociationTypeInterface $associationType,
        AssociationTypeTranslation $translatable
    ) {
        $associationType->getTranslation()->willReturn($translatable);
        $translatable->setLabel('Vente croisée')->shouldBeCalled();
        $associationType->setCode('mycode')->shouldBeCalled();
        $associationType->setLocale('fr_FR')->shouldBeCalled();
        $associationType->getId()->willReturn(null);

        $values = [
            'code'   => 'mycode',
            'labels' => [
                'fr_FR' => 'Vente croisée',
            ],
        ];

        $this->update($associationType, $values, []);
    }
}
