<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Description of AbstractTranslatableException
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class AbstractTranslatableException extends \Exception implements TranslatableExceptionInterface
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

    public function getRawMessage()
    {
        return $this->rawMessage;
    }

    public function getMessageParameters()
    {
        return $this->messageParameters;
    }

    /**
     * Translates the message in the current locale
     *
     * @param TranslatorInterface $translator
     */
    public function translateMessage(TranslatorInterface $translator)
    {
        $this->message = $translator->trans($this->rawMessage, $this->messageParameters);
    }
}
