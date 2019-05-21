<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Onboarder;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class RecordLabels
{
    /** @var string */
    private $identifier;

    /** @var array  */
    private $labels;

    /** @var string */
    private $code;

    /** @var string */
    private $referenceEntityIdentifier;

    public function __construct(string $identifier, array $labels, string $code, string $referenceEntityIdentifier)
    {
        $this->identifier = $identifier;
        $this->labels = $labels;
        $this->code = $code;
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
    }
}
