<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

interface MassActionResponseInterface
{
    /**
     * @return boolean
     */
    public function isSuccessful();

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param string $name
     * @return mixed
     */
    public function getOption($name);
}
