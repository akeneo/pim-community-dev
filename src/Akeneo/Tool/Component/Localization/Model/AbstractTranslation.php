<?php

namespace Akeneo\Tool\Component\Localization\Model;

/**
 * Abstract translation class
 */
abstract class AbstractTranslation
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $foreignKey;

    /**
     * Get id
     *
     * @return int $id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set locale
     *
     * @param string $locale
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Set foreignKey
     *
     * @param string $foreignKey
     */
    public function setForeignKey(string $foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get foreignKey
     *
     * @return string $foreignKey
     */
    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }
}
