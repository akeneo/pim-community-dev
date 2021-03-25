<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Message;

use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\Event;
use Webmozart\Assert\Assert;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdated extends Event
{
    /**
     * @var array{identifier: string, origin: string} $data
     */
    public function __construct(Author $author, array $data, int $timestamp = null, string $uuid = null)
    {
        Assert::keyExists($data, 'identifier');
        Assert::stringNotEmpty($data['identifier']);

        parent::__construct($author, $data, $timestamp, $uuid);
    }

    public function getName(): string
    {
        return 'product.updated';
    }

    public function getIdentifier(): string
    {
        return $this->data['identifier'];
    }
}
