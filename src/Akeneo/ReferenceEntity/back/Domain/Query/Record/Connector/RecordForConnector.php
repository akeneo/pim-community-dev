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

namespace Akeneo\ReferenceEntity\Domain\Query\Record\Connector;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordForConnector
{
    /** @var RecordCode */
    private $code;

    /** @var LabelCollection */
    private $labelCollection;

    /** @var Image */
    private $image;

    /** @var array */
    private $normalizedValues;

    public function __construct(
        RecordCode $code,
        LabelCollection $labelCollection,
        Image $image,
        array $normalizedValues
    ) {
        $this->code = $code;
        $this->labelCollection = $labelCollection;
        $this->image = $image;
        $this->normalizedValues = $normalizedValues;
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'values' => $this->normalizedValues,
            'main_image' => $this->image->isEmpty() ? null : $this->image->getKey()
        ];
    }
}
