<?php

declare(strict_types=1);

namespace spec\Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Pim\Enrichment\Component\Product\Query\AssociationType\GetAssociationTypeCodes;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Test\Acceptance\AssociationType\InMemoryAssociationTypeRepository;
use Akeneo\Test\Acceptance\AssociationType\InMemoryGetAssociationTypeCodes;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class InMemoryGetAssociationTypeCodesSpec extends ObjectBehavior
{
    function let()
    {
        $associationTypeRepository = new InMemoryAssociationTypeRepository();
        $associationType1 = new AssociationType();
        $associationType1->setCode('code1');
        $associationTypeRepository->save($associationType1);
        $associationType2 = new AssociationType();
        $associationType2->setCode('code2');
        $associationTypeRepository->save($associationType2);
        $associationType3 = new AssociationType();
        $associationType3->setCode('code3');
        $associationTypeRepository->save($associationType3);

        $this->beConstructedWith($associationTypeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGetAssociationTypeCodes::class);
    }

    function it_implements_get_association_type_codes_interface()
    {
        $this->shouldImplement(GetAssociationTypeCodes::class);
    }

    function it_returns_association_codes()
    {
        $results = $this->findAll();

        $results->shouldImplement(\Iterator::class);
        $array = iterator_to_array($results->getWrappedObject());
        Assert::eq($array, ['code1', 'code2', 'code3']);
    }
}
