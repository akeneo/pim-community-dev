<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Lock\Query;

/**
 * TODO pull up remove this interface
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EnsureLockTableExistsInterface
{
    public function execute(): void;
}
