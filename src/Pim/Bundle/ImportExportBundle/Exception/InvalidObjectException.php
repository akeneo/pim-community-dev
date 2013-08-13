<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

use Symfony\Component\Form\FormInterface;

/**
 * Invalid object exception
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidObjectException extends \Exception
{
    /**
     * @param FormInterface $form
     */
    public function __construct(FormInterface $form)
    {
        parent::__construct($this->printErrorMessage($form));
    }

    private function printErrorMessage(FormInterface $form)
    {
        $messages = $this->getErrorMessages($form);

        return str_replace(array("Array\n(","\n)\n", '    '), '', print_r($messages, true));
    }

    private function getErrorMessages(FormInterface $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            $template = $error->getMessageTemplate();
            $parameters = $error->getMessageParameters();

            foreach ($parameters as $var => $value) {
                $template = str_replace($var, $value, $template);
            }

            $errors[$key] = $template;
        }

        if ($form->count()) {
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }

        return $errors;
    }
}
