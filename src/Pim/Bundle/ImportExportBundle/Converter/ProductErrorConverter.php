<?php

namespace Pim\Bundle\ImportExportBundle\Converter;

use Symfony\Component\Form\FormInterface;

/**
 * Convert some form validation error to processor entity error
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductErrorConverter
{
    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function convert($form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            // TODO : deal with message translation
            $errors[] = $error->getMessage();
        }

        $formErrors = $this->getChildErrorMessages($form);
        if (isset($formErrors['values'])) {
            foreach ($formErrors['values'] as $field => $message) {
                // TODO : deal with message translation
                $message = explode('|', current($message['text']));
                $message = $message[0];
                $errors[] = sprintf('%s %s', $field, $message);
            }
        }

        return $errors;
    }

    /**
     * Get child error messages
     *
     * @param FormInterface $form
     *
     * @return array
     */
    public function getChildErrorMessages(FormInterface $form)
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

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getChildErrorMessages($child);
                }
            }
        }

        return $errors;
    }
}
