<?php

namespace Pim\Component\Connector\Reader\File;

/**
 * {description}
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileIteratorException extends \Exception
{
    /** @var array */
    protected $item;

    /**
     * FileIteratorException constructor.
     *
     * @param string          $message
     * @param array           $item
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(
        $message,
        array $item,
        $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->item = $item;
    }

    /**
     * @return array
     */
    public function getItem()
    {
        return $this->item;
    }
}
