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
class ProductRemoved extends Event
{
    /**
     * @var array{identifier: string, category_codes: array<string>, origin: string} $data
     */
    public function __construct(Author $author, array $data, int $timestamp = null, string $uuid = null)
    {
        Assert::keyExists($data, 'identifier');
        Assert::stringNotEmpty($data['identifier']);

        Assert::keyExists($data, 'category_codes');
        Assert::allStringNotEmpty($data['category_codes']);

        parent::__construct($author, $data, $timestamp, $uuid);
    }

    public function getName(): string
    {
        return 'product.removed';
    }

    public function getIdentifier(): string
    {
        return $this->data['identifier'];
    }

    /**
     * @return array<string>
     */
    public function getCategoryCodes(): array
    {
        return $this->data['category_codes'];
    }
}
