<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query;

/**
 * Data transfer objects that holds informations about a reference entity record.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RecordInformation
{
    /** @var string */
    public $referenceEntityIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels;

    public function __construct(string $referenceEntityIdentifier, string $code, array $labels)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
        $this->code = $code;
        $this->labels = $labels;
    }
}
