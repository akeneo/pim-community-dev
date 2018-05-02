<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Exception;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AlreadyExistingAxisValueCombinationException extends \Exception
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $entityClass;

    /** @var string */
    private $axisCombination;

    /**
     * @param string $identifier
     * @param string $entityClass
     * @param string $axisCombination
     */
    public function __construct(string $identifier, string $entityClass, string $axisCombination)
    {
        $this->identifier = $identifier;
        $this->entityClass = $entityClass;
        $this->axisCombination = $axisCombination;

        parent::__construct($this->createExceptionMessage());
    }

    /**
     * @return string
     */
    public function getEntityIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    private function createExceptionMessage(): string
    {
        return sprintf(
            'The %s "%s" already have a value for the "%s" axis combination.',
            $this->entityClass,
            $this->identifier,
            $this->axisCombination
        );
    }
}
