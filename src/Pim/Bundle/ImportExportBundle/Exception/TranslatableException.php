<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Description of TranslatableException
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TranslatableException extends \Exception implements TranslatableExceptionInterface
{
    /**
     * @var string
     */
    protected $rawMessage;

    /**
     * @var array
     */
    protected $messageParameters;

    /**
     * Constructor
     *
     * @param string $rawMessage
     * @param array  $messageParameters
     */
    public function __construct(
        $rawMessage,
        array $messageParameters = array(),
        $code = null,
        \Exception $previous = null
    ) {
        $this->rawMessage = $rawMessage;
        $this->messageParameters = $messageParameters;
        parent::__construct(strtr($rawMessage, $messageParameters), $code, $previous);
    }

    /**
     * Returns the raw exception message
     *
     * @return string
     */
    public function getRawMessage()
    {
        return $this->rawMessage;
    }

    /**
     * Returns the exception message parameters
     *
     * @return array
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function translateMessage(TranslatorInterface $translator)
    {
        $this->message = $translator->trans($this->rawMessage, $this->messageParameters);
    }
}
