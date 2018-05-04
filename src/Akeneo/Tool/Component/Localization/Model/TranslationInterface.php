<?php

namespace Akeneo\Tool\Component\Localization\Model;

/**
 * Translation interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TranslationInterface
{
    /**
     * Get id
     *
     * @return int $id
     */
    public function getId();

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return TranslationInterface
     */
    public function setLocale($locale);

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale();

    /**
     * Set foreignKey
     *
     * @param string $foreignKey
     *
     * @return TranslationInterface
     */
    public function setForeignKey($foreignKey);

    /**
     * Get foreignKey
     *
     * @return string $foreignKey
     */
    public function getForeignKey();
}
