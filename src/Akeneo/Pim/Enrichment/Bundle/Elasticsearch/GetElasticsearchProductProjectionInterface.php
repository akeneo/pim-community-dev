<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Doctrine\DBAL\Exception;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetElasticsearchProductProjectionInterface
{
    /**
     * @param UuidInterface[] $productUuids
     *
     * @return iterable<string, ElasticsearchProductProjection>
     *
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function fromProductUuids(array $productUuids): iterable;

    /**
     * @return iterable<string, ElasticsearchProductProjection>
     *
     * @throws ObjectNotFoundException when one or several of the product are not found
     *
     * @deprecated Use fromProductUuids
     */
    public function fromProductIdentifiers(array $productIdentifiers): iterable;
}
