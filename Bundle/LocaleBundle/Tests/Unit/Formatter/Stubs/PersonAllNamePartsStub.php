<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs;

use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\LocaleBundle\Model\MiddleNameInterface;
use Oro\Bundle\LocaleBundle\Model\NamePrefixInterface;
use Oro\Bundle\LocaleBundle\Model\NameSuffixInterface;

class PersonAllNamePartsStub implements FirstNameInterface, MiddleNameInterface, LastNameInterface, NamePrefixInterface,
 NameSuffixInterface
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
