<?php

namespace Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier;

class CanNotSwitchMainIndentifierWithPublishedProductException extends \Exception
{
    public function __construct()
    {
        parent::__construct('If you would like to change your main identifier, please make sure you unpublish your products first and then change your main identifier.');
    }
}
