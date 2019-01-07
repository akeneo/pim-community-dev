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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;

/**
 * Read model representing the mapped identifier values of a product.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductIdentifierValues
{
    /** @var array */
    private $identifierValues;

    /**
     * @param array $identifierValues
     */
    public function __construct(array $identifierValues)
    {
        foreach (IdentifiersMapping::FRANKLIN_IDENTIFIERS as $franklinCode) {
            $this->identifierValues[$franklinCode] = $identifierValues[$franklinCode] ?? null;
        }
    }

    /**
     * @return array
     */
    public function identifierValues(): array
    {
        return $this->identifierValues;
    }
}
