<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface IdentifiersMappingProviderInterface
{
    /**
     * Updates the identifiers mapping.
     *
     * @param IdentifiersMapping $identifiersMapping
     *
     * @throws DataProviderException
     */
    public function updateIdentifiersMapping(IdentifiersMapping $identifiersMapping): void;
}
