<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Generate and return the CSP javascript nonce.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScriptNonceGenerator
{
    /** @var string */
    private $generatedNonce;
    /** @var RequestStack */
    private $request;

    public function __construct(RequestStack $request)
    {
        $this->request = $request;
    }

    /**
    * For XML http requests, the nonce is read from session to ensure it is the same than the original request.
    */
    public function getGeneratedNonce(): string
    {
        if (null === $this->generatedNonce) {
            $this->generatedNonce = $this->request->getCurrentRequest()->getSession()->get('nonce', null);
        }

        if (null === $this->generatedNonce) {
            $this->generatedNonce = Uuid::uuid4()->toString();
            $this->request->getCurrentRequest()->getSession()->set('nonce', $this->generatedNonce);
        }

        return $this->generatedNonce;
    }
}
