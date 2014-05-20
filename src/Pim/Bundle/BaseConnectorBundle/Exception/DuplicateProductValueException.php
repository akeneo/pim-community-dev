<?php

namespace Pim\Bundle\BaseConnectorBundle\Exception;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\TransformBundle\Exception\ParametrizedExceptionInterface;
use Pim\Bundle\TransformBundle\Exception\ParametrizedException;

/**
 * Exception for duplicate product values that should be unique, used during imports
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateProductValueException extends InvalidItemException implements ParametrizedExceptionInterface
{
    /**
     * @var string
     */
    protected $messageTemplate = 'The value "%value%" for unique attribute "%code%" was already read in this file';

    /**
     * @var array
     */
    protected $messageParameters;

    /**
     * Constructor
     *
     * @param string $code
     * @param string $value
     * @param array  $item
     */
    public function __construct($code, $value, array $item)
    {
        $this->messageParameters = ['%code%'  => $code, '%value%' => $value];
        $exception = new ParametrizedException($this->messageTemplate, $this->messageParameters);

        parent::__construct($exception->getMessage(), $item, $this->messageParameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }
}
