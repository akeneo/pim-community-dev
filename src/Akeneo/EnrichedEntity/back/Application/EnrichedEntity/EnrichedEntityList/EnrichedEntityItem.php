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

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;

/**
 * Read model representing an enriched entity within the list.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityItem
{
    /** @var string */
    public $identifier;

    /** @var array */
    public $labels;

    public static function fromEnrichedEntity(EnrichedEntity $enrichedEntity): self
    {
        $new = new self();
        $new->identifier = (string) $enrichedEntity->getIdentifier();
        $new->labels = [];
        foreach ($enrichedEntity->getLabelCodes() as $labelCode) {
            $new->labels[$labelCode] = $enrichedEntity->getLabel($labelCode);
        }

        return $new;
    }
}
