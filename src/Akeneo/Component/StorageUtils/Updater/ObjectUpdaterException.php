<?php

namespace Akeneo\Component\StorageUtils\Updater;

// a generic exception for the updater
// could extends LogicException but extending InvalidArgumentException allows to keep the custom logic implemented
// elsewhere, guys catching invalid will catch all these news exceptions without issue
class ObjectUpdaterException extends \InvalidArgumentException
{
}