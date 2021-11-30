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

namespace Akeneo\Platform\TailoredExport\Application\Common\Selection\Parent;

final class ParentLabelSelection implements ParentSelectionInterface
{
    public const TYPE = 'label';

    private string $locale;
    private string $channel;

    public function __construct(
        string $locale,
        string $channel
    ) {
        $this->locale = $locale;
        $this->channel = $channel;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }
}
