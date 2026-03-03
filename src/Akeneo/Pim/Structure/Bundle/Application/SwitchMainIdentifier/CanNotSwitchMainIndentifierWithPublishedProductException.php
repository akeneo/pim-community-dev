<?php

namespace Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CanNotSwitchMainIndentifierWithPublishedProductException extends \Exception
{
    public function __construct()
    {
        parent::__construct('If you would like to change your main identifier, please make sure you unpublish your products first and then change your main identifier.');
    }
}
