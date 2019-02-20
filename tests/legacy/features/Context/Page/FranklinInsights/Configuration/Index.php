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

namespace Context\Page\FranklinInsights\Configuration;

use Context\Page\Product\Index as BaseIndex;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class Index extends BaseIndex
{
    /** @var string */
    protected $path = '#/franklin-insights/connection/edit';
}
