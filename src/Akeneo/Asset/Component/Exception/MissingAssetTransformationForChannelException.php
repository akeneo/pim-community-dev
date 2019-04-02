<?php

declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PimEnterprise\Component\ProductAsset\Exception;

/**
 * Exception thrown when a channel has no asset transformation, preventing a variation to be generated for this channel.
 */
class MissingAssetTransformationForChannelException extends \LogicException
{
    private $channelCode;

    public function __construct(string $channelCode)
    {
        $this->channelCode = $channelCode;
    }

    public function getChannelCode(): string
    {
        return $this->channelCode;
    }
}
