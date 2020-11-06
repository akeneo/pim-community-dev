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
     */
    public function getId(): int;

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId(int $id): \Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

    /**
     * Set option
     *
     * @param AttributeOptionInterface $option
     */
    public function setOption(AttributeOptionInterface $option): \Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

    /**
     * Get option
     */
    public function getOption(): \Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;

    /**
     * Get used locale
     *
     * @return string $locale
     */
    public function getLocale(): string;

    /**
     * Set used locale
     *
     * @param string $locale
     */
    public function setLocale(string $locale): \Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

    /**
     * Set value
     *
     * @param string $value
     */
    public function setValue(string $value): \Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

    /**
     * Get value
     */
    public function getValue(): string;

    /**
     * Returns the label of the attribute
     */
    public function getLabel(): string;

    /**
     * Sets the label
     *
     * @param string $label
     */
    public function setLabel(string $label): \Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
}
