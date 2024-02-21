<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

/**
 * This exception is thrown when an entity with family variant uses a combination
 * of variant axis values that already exists.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AlreadyExistingAxisValueCombinationException extends \Exception
{
    /** @var string */
    private $identifier;

    /**
     * @param string $identifier
     * @param string $message
     */
    public function __construct(string $identifier, string $message)
    {
        $this->identifier = $identifier;

        parent::__construct($message);
    }

    /**
     * Returns the identifier of the entity having an already existing axis value combination.
     *
     * @return string
     */
    public function getEntityIdentifier(): string
    {
        return $this->identifier;
    }
}
