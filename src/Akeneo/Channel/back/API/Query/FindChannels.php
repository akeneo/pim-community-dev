<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindChannels
{
    /**
     * @param $codes string[]
     *
     * @return Channel[]
     */
    public function findByCodes(array $codes): array;

    /**
     * @return Channel[]
     */
    public function findAll(): array;
}
