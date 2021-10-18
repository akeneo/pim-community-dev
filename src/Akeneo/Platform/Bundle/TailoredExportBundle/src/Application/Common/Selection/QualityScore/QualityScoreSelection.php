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

namespace Akeneo\Platform\Bundle\TailoredExportBundle\src\Application\Common\Selection\QualityScore;

use Akeneo\Platform\TailoredExport\Application\Common\Selection\SelectionInterface;

class QualityScoreSelection implements SelectionInterface
{
    public const TYPE = 'quality_score';

    private string $channel;
    private string $locale;

    public function __construct(string $channel, string $locale)
    {
        $this->channel = $channel;
        $this->locale = $locale;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
