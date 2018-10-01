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

namespace Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;

/**
 * Read model representing an enriched entity detailled for display purpose (like a form)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityDetails
{
    public const IDENTIFIER = 'identifier';
    public const LABELS = 'labels';
    public const IMAGE = 'image';

    /** @var ReferenceEntityIdentifier */
    public $identifier;

    /** @var LabelCollection */
    public $labels;

    /** @var Image */
    public $image;

    public function normalize(): array
    {
        return [
            self::IDENTIFIER => (string) $this->identifier,
            self::LABELS     => $this->labels->normalize(),
            self::IMAGE      => $this->image->normalize(),
        ];
    }
}
