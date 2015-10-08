<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs;

use Oro\Bundle\LocaleBundle\Model\FullNameInterface;

class PersonFullNameStub implements FullNameInterface
{
    /**
     * @return string
     */
    public function getFirstName()
    {
        return 'fn';
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return 'ln';
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return 'mn';
    }

    /**
     * @return string
     */
    public function getNamePrefix()
    {
        return 'np';
    }

    /**
     * @return string
     */
    public function getNameSuffix()
    {
        return 'ns';
    }
}
