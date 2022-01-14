<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Application\Common\Target;

use Webmozart\Assert\Assert;

class StringCollectionTarget implements Target
{
    public function __construct(private string $name, private string $type, private bool $required)
    {
        Assert::eq('string_collection', $type, 'The type of the target must be "string_collection".');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }
}
