<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo;

use Oro\Bundle\DataFlowBundle\Configuration\AbstractConfiguration;
use JMS\Serializer\Annotation\Type;

/**
 * Demo configuration
 *
 *
 */
class MyConfiguration extends AbstractConfiguration
{

    /**
     * @Type("string")
     * @var string
     */
    public $charset = 'UTF-8';

    /**
     * @Type("string")
     * @var string
     */
    public $delimiter = ';';

    /**
     * @Type("string")
     * @var string
     */
    public $enclosure = '"';

    /**
     * @Type("string")
     * @var string
     */
    public $escape = '\\';

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     *
     * @return CsvConfiguration
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     *
     * @return CsvConfiguration
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * @param string $enclosure
     *
     * @return CsvConfiguration
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * @return string
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * @param string $escape
     *
     * @return CsvConfiguration
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormTypeServiceId()
    {
        return "my_configuration";
    }
}
