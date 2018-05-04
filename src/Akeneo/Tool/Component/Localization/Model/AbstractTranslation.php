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
     * @param string $foreignKey
     *
     * @return AbstractTranslation
     */
    public function setForeignKey($foreignKey)
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get foreignKey
     *
     * @return string $foreignKey
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
}
