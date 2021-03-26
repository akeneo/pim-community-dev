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
class ProductModelUpdated extends Event
{
    /**
     * @var array{code: string, origin: string} $data
     */
    public function __construct(Author $author, array $data, int $timestamp = null, string $uuid = null)
    {
        Assert::keyExists($data, 'code');
        Assert::stringNotEmpty($data['code']);

        parent::__construct($author, $data, $timestamp, $uuid);
    }

    public function getName(): string
    {
        return 'product_model.updated';
    }

    public function getCode(): string
    {
        return $this->data['code'];
    }
}
