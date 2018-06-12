<?php

namespace spec\Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use PhpSpec\ObjectBehavior;

class FindEnrichedEntityQuerySpec extends ObjectBehavior
{
    public function let(EnrichedEntityRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\FindEnrichedEntityQuery::class);
    }

    function it_returns_an_enriched_entity_given_its_identifier(
        $repository,
        EnrichedEntity $enrichedEntity
    ) {
        $identifier = EnrichedEntityIdentifier::fromString('designer');
        $enrichedEntity->getIdentifier()->willReturn($identifier);
        $enrichedEntity->getLabelCodes()->willReturn(['fr_FR', 'en_US']);
        $enrichedEntity->getLabel('fr_FR')->willReturn('Concepteur');
        $enrichedEntity->getLabel('en_US')->willReturn('Designer');
        $repository->findOneByIdentifier($identifier)->willReturn($enrichedEntity);

        $expectedDetails = EnrichedEntityDetails::fromEntity($enrichedEntity->getWrappedObject());
        $this->__invoke('designer')->shouldHaveTheEnrichedEntityDetails($expectedDetails);
    }

    function it_returns_null_if_there_enriched_entity_does_not_exist_for_the_given_identifier(
        $repository
    ) {
        $identifier = EnrichedEntityIdentifier::fromString('sofa');
        $repository->findOneByIdentifier($identifier)->willReturn(null);

        $this->__invoke($identifier)->shouldReturn(null);
    }

    function getMatchers()
    {
        return [
            'haveTheEnrichedEntityDetails' => function (
                EnrichedEntityDetails $expectedDetails,
                EnrichedEntityDetails $actualDetails
            ) {
                $hasSameLabels = 0 === count(
                        array_merge(
                            array_diff($expectedDetails->labels, $actualDetails->labels),
                            array_diff($actualDetails->labels, $expectedDetails->labels)
                        )
                    );
                return $expectedDetails->identifier === $actualDetails->identifier && $hasSameLabels;
            },
        ];
    }
}
