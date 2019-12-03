<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Ramsey\Uuid\Uuid;

/**
 * Generate and return the CSP javascript nonce
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScriptNonceGenerator
{
    /** @var string */
    private $generatedNonce;

    public function getGeneratedNonce(): string
    {
        if (null === $this->generatedNonce) {
            $this->generatedNonce = Uuid::uuid4()->toString();
        }

        return $this->generatedNonce;
    }
}
