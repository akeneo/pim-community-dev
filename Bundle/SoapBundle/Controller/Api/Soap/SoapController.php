<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Soap;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapApiCrudInterface;
use Oro\Bundle\SoapBundle\Controller\Api\FormAwareInterface;
use Oro\Bundle\SoapBundle\Controller\Api\FormHandlerAwareInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

abstract class SoapController extends SoapGetController implements
     FormAwareInterface,
     FormHandlerAwareInterface,
     SoapApiCrudInterface
{
    /**
     * {@inheritDoc}
     */
    public function handleUpdateRequest($id)
    {
        return $this->processForm($this->getEntity($id));
    }

    /**
     * {@inheritDoc}
     */
    public function handleCreateRequest()
    {
        return $this->processForm($this->getManager()->createEntity());
    }

    /**
     * {@inheritDoc}
     */
    public function handleDeleteRequest($id)
    {
        $entity = $this->getEntity($id);

        $em = $this->getManager()->getObjectManager();
        $em->remove($entity);
        $em->flush();

        return true;
    }

    /**
     * Form processing
     *
     * @param mixed $entity Entity object
     * @return bool True on success
     * @throws \SoapFault
     */
    protected function processForm($entity)
    {
        $this->fixRequestAttributes($entity);
        if (!$this->getFormHandler()->process($entity)) {
            throw new \SoapFault('BAD_REQUEST', $this->getFormErrors($this->getForm()));
        }

        return true;
    }

    /**
     * @param FormInterface $form
     * @return string All form's error messages concatenated into one string
     */
    protected function getFormErrors(FormInterface $form)
    {
        $errors = '';

        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {
            $errors .= $error->getMessage() ."\n";
        }

        foreach ($form->all() as $key => $child) {
            if ($err = $this->getFormErrors($child)) {
                $errors .= sprintf("%s: %s\n", $key, $err);
            }
        }

        return $errors;
    }

    /**
     * Convert SOAP request to format applicable for form.
     *
     * @param object $entity
     */
    protected function fixRequestAttributes($entity)
    {
        $request = $this->container->get('request');
        $entityData = $request->get($this->getForm()->getName());
        if (!is_object($entityData)) {
            return;
        }

        $data = array();
        foreach ((array)$entityData as $field => $value) {
            // special case for ordered arrays
            if ($value instanceof \stdClass && isset($value->item) && is_array($value->item)) {
                $value = (array) $value->item;
            }

            if ($value instanceof Collection) {
                $value = $value->toArray();
            }

            if (!is_null($value)) {
                $data[preg_replace('/[^\w+]+/i', '', $field)] = $value;
            }
        }

        $request->request->set($this->getForm()->getName(), $data);
    }
}
