<?php


namespace Akeneo\Tool\Component\Connector\Exception;

/**
 * Class BusinessArrayConversionException
 * is used when an exception has to be thrown for action in the UI
 * that is the reason for the internationalisation parameters.
 * @package Akeneo\Tool\Component\Connector\Exception
 */
class BusinessArrayConversionException extends ArrayConversionException
{
    /** @var string */
    private $messageKey;
    /** @var  array */
    private $messageParameters;

    public function __construct($message, string $messageKey, array  $messageParameters, ?\Throwable $previous = null, $code = 0)
    {
        parent::__construct($message, $code, $previous);
        $this->messageKey = $messageKey;
        $this->messageParameters = $messageParameters;
    }

    /**
     * @return string
     */
    public function getMessageKey()
    {
        return $this->messageKey;
    }

    /**
     * @return array
     */
    public function getMessageParameters(): array
    {
        return $this->messageParameters;
    }
}
