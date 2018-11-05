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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model\Write;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOption
{
    /** @var string */
    private $franklinOptionId;

    /** @var string */
    private $franklinOptionLabel;

    /** @var null|string */
    private $pimOptionId;

    /** @var null|string */
    private $pimOptionLabel;

    /**
     * @param string $franklinOptionId
     * @param string $franklinOptionLabel
     * @param null|string $pimOptionId
     * @param null|string $pimOptionLabel
     */
    public function __construct(
        string $franklinOptionId,
        string $franklinOptionLabel,
        ?string $pimOptionId = null,
        ?string $pimOptionLabel = null
    ) {
        $this->franklinOptionId = $franklinOptionId;
        $this->franklinOptionLabel = $franklinOptionLabel;
        $this->pimOptionId = $pimOptionId;
        $this->pimOptionLabel = $pimOptionLabel;
    }
}
