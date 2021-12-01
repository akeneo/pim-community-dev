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

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorReferenceEntity
{
    public function __construct(private ReferenceEntityIdentifier $identifier, private LabelCollection $labelCollection, private Image $image)
    {
    }

    public function normalize(): array
    {
        $normalizedLabels = $this->labelCollection->normalize();
        return [
            'code' => $this->identifier->normalize(),
            'labels' => empty($normalizedLabels) ? (object) [] : $normalizedLabels,
            'image' => $this->image->isEmpty() ? null : $this->image->getKey()
        ];
    }

    public function getIdentifier(): ReferenceEntityIdentifier
    {
        return $this->identifier;
    }
}
