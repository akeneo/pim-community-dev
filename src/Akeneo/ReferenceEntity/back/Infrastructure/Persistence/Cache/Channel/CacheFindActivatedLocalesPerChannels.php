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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Cache\Channel;

use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CacheFindActivatedLocalesPerChannels implements FindActivatedLocalesPerChannelsInterface
{
    /** @var null|array */
    private $activatedLocalesPerChannels;

    /** @var FindActivatedLocalesPerChannelsInterface */
    private $findActivatedLocalesPerChannels;

    public function __construct(FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels)
    {
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
    }

    public function __invoke(): array
    {
        if (null === $this->activatedLocalesPerChannels) {
            $this->activatedLocalesPerChannels = ($this->findActivatedLocalesPerChannels)();
        }

        return $this->activatedLocalesPerChannels;
    }
}
