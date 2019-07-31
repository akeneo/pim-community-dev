<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFamilyMask
{
    /** @var string */
    private $familyCode;

    /** @var CompletenessFamilyMaskPerChannelAndLocale[] */
    private $masks;

    public function __construct(
        string $familyCode,
        array $masksByChannel
    ) {
        $this->familyCode = $familyCode;
        $this->masks = $masksByChannel;
    }

    /**
     * @return CompletenessFamilyMaskPerChannelAndLocale[]
     */
    public function masks(): array
    {
        return $this->masks;
    }
}
