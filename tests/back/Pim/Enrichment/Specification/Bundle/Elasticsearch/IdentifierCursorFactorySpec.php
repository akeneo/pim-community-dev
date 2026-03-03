<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierCursor;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierCursorFactory;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierCursorFactorySpec extends ObjectBehavior
{
    function let(Client $searchEngine)
    {
        $this->beConstructedWith($searchEngine, 100);
    }

    function it_is_a_cursor_factory()
    {
        $this->shouldHaveType(IdentifierCursorFactory::class);
        $this->shouldImplement(CursorFactoryInterface::class);
    }

    function it_creates_a_cursor(Client $searchEngine)
    {
        $this->createCursor([], [])->shouldBeLike(new IdentifierCursor(
            $searchEngine->getWrappedObject(),
            ['_source' => ['identifier', 'document_type', 'id']],
            100
        ));
        $this->createCursor(['_source' => 'values', 'foo' => 'bar'], ['page_size' => 62])->shouldBeLike(new IdentifierCursor(
            $searchEngine->getWrappedObject(),
            ['_source' => ['identifier', 'document_type', 'id'], 'foo' => 'bar'],
            62
        ));
    }
}
