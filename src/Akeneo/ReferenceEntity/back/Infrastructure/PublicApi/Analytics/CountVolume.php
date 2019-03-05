<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

/**
 * Represents the volume of an axis of limitation.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountVolume
{
    /** @var int */
    private $volume;

    public function __construct(int $volume)
    {
        $this->volume = $volume;
    }

    public function getVolume(): int
    {
        return $this->volume;
    }
}
