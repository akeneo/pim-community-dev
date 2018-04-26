<?php

declare(strict_types=1);

namespace Context\Page\Client;

use Context\Page\Base\Grid;

/**
 * Client creation page
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Creation extends Grid
{
    /**
     * @var string
     */
    protected $path = '#/client/';

    /**
     * Find a validation tooltip containing a text
     *
     * @param string $text
     *
     * @return null|Element
     */
    public function findValidationTooltip(string $text)
    {
        return $this->spin(function () use ($text) {
            return $this->find(
                'css',
                sprintf(
                    '.validation-errors .error-message:contains("%s")',
                    $text
                )
            );
        }, sprintf('Cannot find error message "%s" in validation tooltip', $text));
    }
}
