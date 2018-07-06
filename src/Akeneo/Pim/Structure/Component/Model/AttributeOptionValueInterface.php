<?php

namespace Akeneo\Pim\Structure\Component\Model;

/**
 * Attribute option values
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AttributeOptionValueInterface
{
    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $id
     *
     * @return AttributeOptionValueInterface
     */
    public function setId($id);

    /**
     * Set option
     *
     * @param AttributeOptionInterface $option
     *
     * @return AttributeOptionValueInterface
     */
    public function setOption(AttributeOptionInterface $option);

    /**
     * Get option
     *
     * @return AttributeOptionInterface
     */
    public function getOption();

    /**
     * Get used locale
     *
     * @return string $locale
     */
    public function getLocale();

    /**
     * Set used locale
     *
     * @param string $locale
     *
     * @return AttributeOptionValueInterface
     */
    public function setLocale($locale);

    /**
     * Set value
     *
     * @param string $value
     *
     * @return AttributeOptionValueInterface
     */
    public function setValue($value);

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

    /**
     * Returns the label of the attribute
     *
     * @return string
     */
    public function getLabel();

    /**
     * Sets the label
     *
     * @param string $label
     *
     * @return AttributeOptionValueInterface
     */
    public function setLabel($label);
}
