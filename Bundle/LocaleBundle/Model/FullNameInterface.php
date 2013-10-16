<?php

namespace Oro\Bundle\LocaleBundle\Model;

interface FullNameInterface
{
    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @return string
     */
    public function getMiddleName();

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @return string
     */
    public function getNamePrefix();

    /**
     * @return string
     */
    public function getNameSuffix();
}