<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;

/**
 * Attribute options
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AttributeOptionInterface extends ReferableInterface, VersionableInterface
{
    public function getId(): ?int;

    public function setId(int $id): AttributeOptionInterface;

    public function getAttribute(): ?AttributeInterface;

    public function setAttribute(?AttributeInterface $attribute = null): AttributeOptionInterface;

    public function getOptionValues(): \ArrayAccess|array;

    public function getLocale(): ?string;

    public function setLocale(string $locale): AttributeOptionInterface;

    public function setSortOrder(int $sortOrder): AttributeOptionInterface;

    public function getSortOrder(): ?int;

    public function setCode(string $code): AttributeOptionInterface;

    public function getCode(): ?string;

    public function getTranslation(): ?AttributeOptionValueInterface;

    public function addOptionValue(AttributeOptionValueInterface $value): AttributeOptionInterface;

    public function removeOptionValue(AttributeOptionValueInterface $value): AttributeOptionInterface;

    public function getOptionValue(): ?AttributeOptionValueInterface;

    public function __toString(): string;
}
