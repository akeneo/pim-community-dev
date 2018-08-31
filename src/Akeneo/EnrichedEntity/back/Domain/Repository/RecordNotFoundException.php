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
            $identifier->__toString()
        );

        return new self($message);
    }
}
