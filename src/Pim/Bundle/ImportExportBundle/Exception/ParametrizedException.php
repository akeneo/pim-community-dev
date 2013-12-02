<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

/**
 * An exception with message parameters
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParametrizedException extends \Exception implements ParametrizedExceptionInterface
{
    /**
     * @var string
     */
    protected $messageTemplate;

    /**
     * @var array
     */
    protected $messageParameters;

    /**
     * Constructor
     *
     * @param string $messageTemplate
     * @param array  $messageParameters
     */
    public function __construct(
        $messageTemplate,
        array $messageParameters = array(),
        $code = null,
        \Exception $previous = null
    ) {
        $this->messageTemplate = $messageTemplate;
        $this->messageParameters = $messageParameters;
        parent::__construct(strtr($messageTemplate, $messageParameters), $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParameters()
    {
        return $this->messageParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageTemplate()
    {
        return $this->messageTemplate;
    }
}
