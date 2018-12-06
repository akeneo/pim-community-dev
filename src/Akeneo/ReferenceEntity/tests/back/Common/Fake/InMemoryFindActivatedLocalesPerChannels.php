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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;

class InMemoryFindActivatedLocalesPerChannels implements FindActivatedLocalesPerChannelsInterface
{
    /** @var array */
    private $activatedLocalesPerChannels = [];

    /**
     * {@inheritdoc}
     */
    public function __invoke(): array
    {
        return $this->activatedLocalesPerChannels;
    }

    public function save(string $channel, array $locales): void
    {
        $this->activatedLocalesPerChannels[$channel] = $locales;
    }
}
