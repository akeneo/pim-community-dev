<?php

namespace Pim\Bundle\BatchBundle\Model;

/**
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Step
{
    protected $name;
    protected $reader;
    protected $processor;
    protected $writer;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setReader($reader)
    {
        $this->reader = $reader;

        return $this;
    }

    public function getReader()
    {
        return $this->reader;
    }

    public function setProcessor($processor)
    {
        $this->processor = $processor;

        return $this;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function setWriter($writer)
    {
        $this->writer = $writer;

        return $this;
    }

    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration()
    {
        return array(
            'reader'    => $this->getReader()->getConfiguration(),
            'processor' => $this->getProcessor()->getConfiguration(),
            'writer'    => $this->getWriter()->getConfiguration(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setConfiguration(array $config)
    {
        $this->getReader()->setConfiguration($config['reader']);
        $this->getProcessor()->setConfiguration($config['processor']);
        $this->getWriter()->setConfiguration($config['writer']);
    }
}
