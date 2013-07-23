<?php

namespace Pim\Bundle\BatchBundle\Configuration;

use JMS\Serializer\Annotation\Type;

/**
 * Csv configuration class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CsvConfiguration extends AbstractConfiguration
{
    /**
     * @var string $charset
     *
     * @Type("string")
     */
    protected $charset;

    /**
     * @var string $delimiter
     *
     * @Type("string")
     */
    protected $delimiter;

    /**
     * @var string $enclosure
     *
     * @Type("string")
     */
    protected $enclosure;

    /**
     * @var string $escape
     *
     * @Type("string")
     */
    protected $escape;

    /**
     * Get charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set charset
     *
     * @param string $charset
     *
     * @return \Pim\Bundle\BatchBundle\Configuration\CsvConfiguration
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Get delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set delimiter
     *
     * @param string $delimiter
     *
     * @return \Pim\Bundle\BatchBundle\Configuration\CsvConfiguration
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Get enclosure
     *
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set enclosure
     *
     * @param string $enclosure
     *
     * @return \Pim\Bundle\BatchBundle\Configuration\CsvConfiguration
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Get escape
     *
     * @return string
     */
    public function getEscape()
    {
        return $this->escape;
    }

    /**
     * Set escape
     *
     * @param string $escape
     *
     * @return \Pim\Bundle\BatchBundle\Configuration\CsvConfiguration
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormTypeServiceId()
    {
        return 'pim_configuration_csv';
    }
}
