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

namespace Akeneo\EnrichedEntity\Domain\Repository;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordNotFoundException extends \RuntimeException
{
    public static function withIdentifier(RecordIdentifier $identifier): self
    {
        $message = sprintf(
            'Could not find record with identifier "%s"',
            (string) $identifier
        );

        return new self($message);
    }

    public static function withCode(EnrichedEntityIdentifier $enrichedEntityIdentifier, RecordCode $code): self
    {
        $message = sprintf(
            'Could not find record with code "%s" for enriched entity "%s"',
            (string) $code,
            (string) $enrichedEntityIdentifier
        );

        return new self($message);
    }
}
