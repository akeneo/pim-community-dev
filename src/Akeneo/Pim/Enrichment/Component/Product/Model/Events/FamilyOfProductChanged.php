<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Events;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyOfProductChanged implements ProductEvent
{
    use ProductEventTrait;

    /** @var string */
    private $formerFamilyCode;

    /** @var string */
    private $newFamilyCode;

    public function __construct(string $formerFamilyCode, string $newFamilyCode)
    {
        $this->formerFamilyCode = $formerFamilyCode;
        $this->newFamilyCode = $newFamilyCode;
    }

    public function formerFamilyCode(): string
    {
        return $this->formerFamilyCode;
    }

    public function newFamilyCode(): string
    {
        return $this->newFamilyCode;
    }
}
