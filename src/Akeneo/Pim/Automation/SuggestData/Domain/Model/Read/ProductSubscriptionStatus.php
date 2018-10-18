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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model\Read;

/**
 * Read model containing the status of a product subscription to Franklin.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ProductSubscriptionStatus
{
    /** @var bool */
    private $isSubscribed;

    /**
     * @param bool $isSubscribed
     */
    public function __construct(bool $isSubscribed)
    {
        $this->isSubscribed = $isSubscribed;
    }

    /**
     * @return array
     */
    public function normalize(): array
    {
        return [
            'isSubscribed' => $this->isSubscribed,
        ];
    }
}
