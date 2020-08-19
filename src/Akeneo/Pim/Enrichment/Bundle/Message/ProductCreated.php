<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Message;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreated extends BusinessEvent
{
    const NAME = 'product.created';

    public function __construct(string $author, array $data, $timestamp = null, $uuid = null)
    {
        parent::__construct(self::NAME, $author, $data, $timestamp, $uuid);
    }
}
