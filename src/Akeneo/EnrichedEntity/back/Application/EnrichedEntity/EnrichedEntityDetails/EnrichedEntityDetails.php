<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity;

/**
 * Read model representing an enriched entity detail information.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnrichedEntityDetails
{
    /** @var string */
    public $identifier;

    /** @var array */
    public $labels;

    public static function fromEntity(EnrichedEntity $enrichedEntity): self
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
