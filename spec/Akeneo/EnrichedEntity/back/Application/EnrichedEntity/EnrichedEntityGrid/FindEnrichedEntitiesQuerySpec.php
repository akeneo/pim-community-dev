<?php

namespace spec\Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList\EnrichedEntityItem;
use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList\FindEnrichedEntitiesQuery;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Repository\EnrichedEntityRepository;
use PhpSpec\ObjectBehavior;

class FindEnrichedEntitiesQuerySpec extends ObjectBehavior
{
    public function let(EnrichedEntityRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FindEnrichedEntitiesQuery::class);
    }

    function it_returns_a_collection_of_enriched_entity_if_there_is_enriched_entities_in_database(
        $repository,
        EnrichedEntity $enrichedEntity
    ) {
        $enrichedEntity->getIdentifier()->willReturn(EnrichedEntityIdentifier::fromString('designer'));
        $enrichedEntity->getLabelCodes()->willReturn(['fr_FR', 'en_US']);
        $enrichedEntity->getLabel('fr_FR')->willReturn('Concepteur');
        $enrichedEntity->getLabel('en_US')->willReturn('Designer');

        $repository->all()->willReturn([$enrichedEntity]);
        $enrichedEntityItem = EnrichedEntityItem::fromEnrichedEntity($enrichedEntity->getWrappedObject());

        $this->__invoke()->shouldBeEnrichedEntityItems([$enrichedEntityItem]);
    }

    function it_returns_null_if_there_enriched_entity_does_not_exist_for_the_given_identifier(
        $repository
    ) {
        $repository->all()->willReturn([]);

        $this->__invoke()->shouldReturn([]);
    }

    public function getMatchers()
    {
        return [
            'beEnrichedEntityItems' => function (
                array $expectedItems,
                array $actualItems
            ) {
                $isSameCollection = true;
                foreach ($expectedItems as $i => $expectedItem) {
                    $actualItem = $actualItems[$i];
                    $hasSameLabels = 0 === count(
                            array_merge(
                                array_diff($expectedItem->labels, $actualItem->labels),
                                array_diff($actualItem->labels, $expectedItem->labels)
                            )
                        );
                    $isSameCollection = $isSameCollection ||
                        ($expectedItem->identifier === $actualItem->identifier && $hasSameLabels);
                }

                return $isSameCollection;
            }
        ];
    }
}
