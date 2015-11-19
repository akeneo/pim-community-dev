<?php

namespace Akeneo\Component\Buffer;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface BufferFactoryInterface
{
    /**
     * Return a new buffer instance
     *
     * @return BufferInterface
     */
    public function create();
}
