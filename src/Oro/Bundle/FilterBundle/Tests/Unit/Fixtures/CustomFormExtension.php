<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Fixtures;

use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\Form\FormTypeInterface;

class CustomFormExtension extends AbstractExtension
{
    /**
     * @var FormTypeInterface[] An array of FormTypeInterface instances
     */
    protected $initialTypes = [];

    /**
     * @param array $initialTypes
     */
    public function __construct(array $initialTypes)
    {
        $this->initialTypes = $initialTypes;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadTypes()
    {
        return $this->initialTypes;
    }
}
