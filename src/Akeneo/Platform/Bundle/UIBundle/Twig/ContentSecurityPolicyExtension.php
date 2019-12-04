<?php

namespace Akeneo\Platform\Bundle\UIBundle\Twig;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;

/**
 * CSP twig extension.
 *
 * This extension can inject a nonce in javascript tags to make them pass the CSP policy..
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentSecurityPolicyExtension extends \Twig_Extension
{
    /** @var ScriptNonceGenerator */
    private $scriptNonceGenerator;

    public function __construct(ScriptNonceGenerator $scriptNonceGenerator)
    {
        $this->scriptNonceGenerator = $scriptNonceGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('js_nonce', [$this, 'getScriptNonce']),
        ];
    }

    public function getScriptNonce()
    {
        return $this->scriptNonceGenerator->getGeneratedNonce();
    }
}
