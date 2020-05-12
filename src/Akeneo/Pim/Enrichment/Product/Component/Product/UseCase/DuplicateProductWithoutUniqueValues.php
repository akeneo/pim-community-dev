<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Component\Product\UseCase;

/**
 * @author    Christophe Chausseray
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DuplicateProductWithoutUniqueValues
{
    /** @var string */
    private $productId;

    /** @var string */
    private $identifier;

    public function __construct(string $productId, string $identifier)
    {
        $this->productId = $productId;
        $this->identifier = $identifier;
    }
    /**
     * @return string
     */
    public function productId(): string
    {
        return $this->productId;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }
}
