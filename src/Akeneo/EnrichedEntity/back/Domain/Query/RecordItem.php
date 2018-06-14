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

namespace Akeneo\EnrichedEntity\back\Domain\Query;

use Akeneo\EnrichedEntity\back\Domain\Model\Record\Record;

/**
 * Read model representing a record within the list.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordItem
{
    /** @var string */
    public $identifier;

    /** @var string */
    public $enrichedEntityIdentifier;

    /** @var array */
    public $labels;
}
