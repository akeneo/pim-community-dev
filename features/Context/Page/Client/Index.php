<?php

declare(strict_types=1);

namespace Context\Page\Client;

use Context\Page\Base\Grid;

/**
 * Client index page
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Index extends Grid
{
    /**
     * @var string
     */
    protected $path = '#/client/';
}
