<?php

namespace Pim\Bundle\RegistryOfCurrentNumberBundle\Model;

use Akeneo\Component\Localization\Model\TranslatableInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Pim\Component\Enrich\Model\ChosableInterface;

/**
 * Channel interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RegistryOfCurrentNumberInterface
{
    const CODE_ATTRIBUTE = 'COUNT_ATTRIBUTE';

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string $code
     *
     * @return RegistryOfCurrentNumberInterface
     */
    public function setCode(string $code): RegistryOfCurrentNumberInterface;

    /**
     * @return int
     */
    public function getValue(): int;

    /**
     * @param int $value
     *
     * @return RegistryOfCurrentNumberInterface
     */
    public function setValue(int $value): RegistryOfCurrentNumberInterface;
}
