<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Generate and return the CSP javascript nonce.
 *
 * The nonce is a generated string used to identify valid inline scripts used in the PIM.
 * Every inline script that will not match this nonce will be blocked for execution.
 * This is mainly used to avoid inline scripts in our wysiwyg editor,
 * but also every text input that could import malicious javascript.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src
 *
 * The generated nonce is used in the AddContentSecurityPolicyListener to add the CSP to the HTTP response.
 * It is also used in Twig template to set the nonce in inline script tags.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScriptNonceGenerator
{
    /** @var RequestStack */
    private $request;
    /** @var string */
    private $kernelSecret;

    public function __construct(RequestStack $request, string $kernelSecret)
    {
        $this->request = $request;
        $this->kernelSecret = $kernelSecret;
    }

    /**
    * For XML http requests, the nonce is read from session to ensure it is the same than the original request.
    */
    public function getGeneratedNonce(): string
    {
        $request = $this->request->getCurrentRequest();
        $bapId = $request->cookies->get('BAPID');

        return hash_hmac('ripemd160', $bapId, $this->kernelSecret);
    }
}
