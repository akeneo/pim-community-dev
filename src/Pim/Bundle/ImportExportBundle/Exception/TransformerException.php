<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

use Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Description of TransformerException
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class TransformerException extends Exception implements TranslatableExceptionInterface
{
    /**
     * @var array
     */
    protected $errors;

    /**
     * @var array
     */
    protected $translatedErrors;

    public function __construct(array $errors, array $item)
    {
        parent::__construct('', $item);
        $this->errors = $errors;
        $this->translatedErrors = array_map(
            function ($args) use ($translator) {
                return strtr($args[1], $args[0]);
            },
            $this->errors
        );
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getTranslatedErrors()
    {
        return $this->translatedErrors;
    }

    public function translateMessage(TranslatorInterface $translator)
    {
        $this->translatedErrors = array_map(
            function ($args) use ($translator) {
                return call_user_func_array(array($translator, 'trans'), $args);
            },
            $this->errors
        );
    }

    public function getMessage()
    {
        return implode(
            "\n",
            array_map(
                $this->translatedErrors,
                function ($error, $field) {
                    return sprintf("%s: %s", $field, $error);
                }
            )
        );
    }
}
