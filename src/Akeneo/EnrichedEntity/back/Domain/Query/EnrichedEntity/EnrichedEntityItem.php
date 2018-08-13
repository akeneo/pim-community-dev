<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;

/**
 * Read model representing an enriched entity for listing purpose (like in a grid)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityItem
{
    public const IDENTIFIER = 'identifier';

    public const LABELS = 'labels';

    /** @var EnrichedEntityIdentifier */
    public $identifier;

    /** @var LabelCollection */
    public $labels;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER => (string) $this->identifier,
            self::LABELS     => $this->labels->normalize()
        ];
    }
}
