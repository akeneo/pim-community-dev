<?php

namespace Context\Page\GroupType;

use Context\Page\Base\Form;

/**
 * !Group type creation page
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /** @var string */
    protected $path = '#/configuration/group-type/create';

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
