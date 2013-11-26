<?php

use Symfony\Component\Translation\TranslatorInterface;

/*
 *  This file is part of XXX
 *  (c) Antoine Guigan <aguigan@qimnet.com>
 *  This source file is subject to the MIT license that is bundled
 *  with this source code in the file LICENSE.
 */

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
