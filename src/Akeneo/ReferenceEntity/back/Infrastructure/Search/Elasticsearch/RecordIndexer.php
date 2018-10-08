<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RecordIndexer implements RecordIndexerInterface
{
    private const INDEX_TYPE = 'pimee_reference_entity_record';
    private const KEY_AS_ID = 'identifier';

    /** @var Client */
    private $recordClient;

    /** @var NormalizerInterface */
    private $normalizer;


    public function __construct(Client $recordClient, NormalizerInterface $normalizer)
    {
        $this->recordClient = $recordClient;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function bulkIndex(array $records)
    {
        if (empty($records)) {
            return;
        }

        $normalizedRecords = array_map(function (Record $record) {
            return $this->normalizer->normalize($record);
        }, $records);

        $this->recordClient->bulkIndexes(self::INDEX_TYPE, $normalizedRecords, self::KEY_AS_ID, Refresh::disable());
    }
}
