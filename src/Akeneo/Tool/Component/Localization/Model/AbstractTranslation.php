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

    /** @var mixed */
    protected $foreignKey;

    /**
     * Get id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return AbstractTranslation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set foreignKey
     *
     * @param mixed $foreignKey
     *
     * @return AbstractTranslation
     */
    public function setForeignKey($foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get foreignKey
     *
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
}
