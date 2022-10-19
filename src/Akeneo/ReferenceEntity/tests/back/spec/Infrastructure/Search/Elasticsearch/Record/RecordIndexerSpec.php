<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexer;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use OpenSpout\Reader\IteratorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIndexerSpec extends ObjectBehavior
{
    function let(Client $recordEsCLient, RecordNormalizerInterface $recordNormalizer)
    {
        $this->beConstructedWith($recordEsCLient, $recordNormalizer, 2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordIndexer::class);
    }

    function it_indexes_one_record(Client $recordEsCLient, RecordNormalizerInterface $recordNormalizer)
    {
        $recordIdentifier = RecordIdentifier::create('designer', 'coco', 'finger');
        $recordNormalizer->normalizeRecord($recordIdentifier)->willReturn(['identifier' => 'stark']);
        $recordEsCLient->index('stark', ['identifier' => 'stark'],
            Argument::type(Refresh::class))
            ->shouldBeCalled();

        $this->index($recordIdentifier);
    }

    function it_index_records_by_reference_entity_identifier_and_by_batch(
        Client $recordEsCLient,
        RecordNormalizerInterface $recordNormalizer,
        IteratorInterface $recordIterator
    ) {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $recordNormalizer->normalizeRecordsByReferenceEntity($referenceEntityIdentifier)->willReturn($recordIterator);
        $recordIterator->valid()->willReturn(true, true, true, false);
        $recordIterator->current()->willReturn(['identifier' => 'stark'], ['identifier' => 'coco'], ['identifier' => 'another_record']);
        $recordIterator->next()->shouldBeCalled();
        $recordIterator->rewind()->shouldBeCalled();

        $recordEsCLient->bulkIndexes([['identifier' => 'stark'], ['identifier' => 'coco']],'identifier', Argument::type(Refresh::class))
            ->shouldBeCalled();
        $recordEsCLient->bulkIndexes([['identifier' => 'another_record']],'identifier', Argument::type(Refresh::class))
            ->shouldBeCalled();

        $this->indexByReferenceEntity($referenceEntityIdentifier);
    }

    function it_removes_one_record(Client $recordEsCLient)
    {
        $recordEsCLient->deleteByQuery(
            [
                "query" => [
                    "bool" => [
                        "must" => [
                            ["term" => ["reference_entity_code" => "designer"]],
                            ["term" => ["code" => "stark"]],
                        ],
                    ],
                ],
            ])->shouldBeCalled();

        $this->removeRecordByReferenceEntityIdentifierAndCode('designer', 'stark');
    }

    function it_removes_all_refenrence_entity_records(Client $recordEsCLient)
    {
        $recordEsCLient->deleteByQuery(
            [
                'query' => [
                    'match' => ['reference_entity_code' => 'designer'],
                ],
            ])->shouldBeCalled();

        $this->removeByReferenceEntityIdentifier('designer');
    }
}
