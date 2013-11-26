<?php

namespace Pim\Bundle\ImportExportBundle\Exception;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Exception for duplicate identifiers in exports
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateIdentifierException extends InvalidItemException implements TranslatableExceptionInterface
{
    protected $translatableException;
    
    /**
     * Constructor
     *
     * @param mixed  $identifier
     * @param string $identifierColumn
     */
    public function __construct($identifier, $identifierColumn, array $item)
    {
        $this->translatableException = new TranslatableException(
            'The "%identifierColumn%" attribute is unique, the value "%identifier%" was already read in this file',
            array(
                '%identifierColumn%' => $identifierColumn,
                '%identifier%'       => $identifier
            )
        );
        parent::__construct($this->translatableException->getMessage(), $item);
        
    }

    public function translateMessage(TranslatorInterface $translator)
    {
        $this->translatableException->translateMessage($translator);
        $this->message = $this->translatableException->getMessage();
    }

}
