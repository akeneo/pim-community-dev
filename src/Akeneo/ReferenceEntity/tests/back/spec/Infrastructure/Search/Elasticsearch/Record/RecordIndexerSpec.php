<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordIndexer;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\RecordNormalizerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
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
        $this->beConstructedWith($recordEsCLient, $recordNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordIndexer::class);
    }

    function it_does_not_index_if_the_list_is_empty(Client $recordEsCLient)
    {
        $recordEsCLient->bulkIndexes(Argument::cetera())->shouldNotBeCalled();
        $this->bulkIndex([]);
    }

    function it_indexes_multiple_records(Client $recordEsCLient, RecordNormalizerInterface $recordNormalizer)
    {
        $stark = Record::create(
            RecordIdentifier::create('designer', 'stark', 'finger'),
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('stark'),
            [
                'fr_FR' => 'Un designer français',
            ],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $coco = Record::create(
            RecordIdentifier::create('designer', 'coco', 'finger'),
            ReferenceEntityIdentifier::fromString('designer'),
            RecordCode::fromString('stark'),
            [
                'fr_FR' => 'Styliste',
            ],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );

        $recordNormalizer->normalize($stark)->willReturn(['identitifer' => 'stark']);
        $recordNormalizer->normalize($coco)->willReturn(['identitifer' => 'coco']);
        $recordEsCLient->bulkIndexes('pimee_reference_entity_record',
            [['identitifer' => 'stark'], ['identitifer' => 'coco']],
            'identifier',
            Argument::type(Refresh::class)
        )->shouldBeCalled();
        $recordEsCLient->refreshIndex()->shouldBeCalled();

        $this->bulkIndex([$stark, $coco]);
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
                "query" => [
                    "match" => ["reference_entity_code" => "designer"],
                ],
            ])->shouldBeCalled();

        $this->removeByReferenceEntityIdentifier('designer');
    }
}
