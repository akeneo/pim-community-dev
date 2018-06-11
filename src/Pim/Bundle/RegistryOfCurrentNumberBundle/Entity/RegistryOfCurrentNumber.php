<?php

namespace Pim\Bundle\RegistryOfCurrentNumberBundle\Entity;

use Pim\Bundle\RegistryOfCurrentNumberBundle\Model\RegistryOfCurrentNumberInterface;

/**
 * RegistryOfCurrentNumber entity
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class RegistryOfCurrentNumber implements RegistryOfCurrentNumberInterface
{
    /** @var int $id */
    protected $id;

    /** @var string $code */
    protected $code;

    /** @var int $value */
    protected $value;

    public function __construct()
    {
        $this->value = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(string $code): RegistryOfCurrentNumberInterface
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(int $value): RegistryOfCurrentNumberInterface
    {
        $this->value = $value;

        return $this;
    }
}
