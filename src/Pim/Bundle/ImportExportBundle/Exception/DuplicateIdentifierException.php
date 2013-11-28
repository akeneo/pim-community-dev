<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Exception for duplicate identifiers in exports
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateIdentifierException extends InvalidItemException implements ParametrizedExceptionInterface
{
    /**
     * @var string
     */
    protected $messageTemplate =
        'The "%identifierColumn%" attribute is unique, the value "%identifier%" was already read in this file';

    /**
     * @var array
     */
    protected $messageParameters;

    /**
     * Constructor
     *
     * @param mixed  $identifier
     * @param string $identifierColumn
     * @param array  $item
     */
    public function __construct($identifier, $identifierColumn, array $item)
    {
        $this->messageParameters = array(
            '%identifierColumn%' => $identifierColumn,
            '%identifier%'       => $identifier
        );
        $parametrizedException = new ParametrizedException(
            $this->messageTemplate,
            $this->messageParameters
        );
        parent::__construct($parametrizedException->getMessage(), $item);
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
