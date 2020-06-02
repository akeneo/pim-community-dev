<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\Documented;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MessageParameterInterface
{
    /**
     * @return string String to be replaced. Must be a string surrounded by "{needle}".
     */
    public function needle(): string;

    public function normalize(): array;
}
