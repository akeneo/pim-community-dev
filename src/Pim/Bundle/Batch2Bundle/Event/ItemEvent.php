<?php

namespace Pim\Bundle\Batch2Bundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ItemEvent extends Event implements EventInterface
{
    const PASSED    = 0;
    const SKIPPED   = 1;
    const PENDING   = 2;
    const UNDEFINED = 3;
    const FAILED    = 4;

    protected $item;

    protected $result = self::UNDEFINED;

    public function __construct($item, $result)
    {
        $this->item   = $item;
        $this->result = $result;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    public function getResult()
    {
        return $this->result;
    }
}
