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

namespace Akeneo\Platform\Syndication\Application\Common\Selection\ReferenceEntity;

final class ReferenceEntityLabelSelection implements ReferenceEntitySelectionInterface
{
    public const TYPE = 'label';

    private string $locale;
    private string $referenceEntityCode;

    public function __construct(
        string $locale,
        string $referenceEntityCode
    ) {
        $this->locale = $locale;
        $this->referenceEntityCode = $referenceEntityCode;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getReferenceEntityCode(): string
    {
        return $this->referenceEntityCode;
    }
}
