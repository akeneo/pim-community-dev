<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TrialEdition\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class Heap implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    private const CUSTOMER_ID = 2875170433;

    private ScriptNonceGenerator $nonceGenerator;

    private ExternalDependenciesFeatureFlag $featureFlag;

    public function __construct(ScriptNonceGenerator $nonceGenerator, ExternalDependenciesFeatureFlag $featureFlag)
    {
        $this->nonceGenerator = $nonceGenerator;
        $this->featureFlag = $featureFlag;
    }

    public function getScript(): string
    {
        if (!$this->featureFlag->isEnabled()) {
            return '';
        }

        $nonce = $this->nonceGenerator->getGeneratedNonce();

        $javascript = <<<JS
<script type="text/javascript" nonce="$nonce">
    window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=document.createElement("script");r.type="text/javascript",r.async=!0,r.src="https://cdn.heapanalytics.com/js/heap-"+e+".js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(r,a);for(var n=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","resetIdentity","removeEventProperty","setEventProperties","track","unsetEventProperty"],o=0;o<p.length;o++)heap[p[o]]=n(p[o])};
    heap.load("%s");
</script>
JS;

        return sprintf($javascript, self::CUSTOMER_ID);
    }

    /**
     * https://developers.heap.io/docs/web#content-security-policy-csp
     */
    public function getContentSecurityPolicy(): array
    {
        if (!$this->featureFlag->isEnabled()) {
            return [];
        }

        return [
            'script-src'  => ["https://cdn.heapanalytics.com", "https://heapanalytics.com", "'unsafe-inline'", "'unsafe-eval'"],
            'img-src'     => ["https://heapanalytics.com"],
            'style-src'   => ["https://heapanalytics.com"],
            'connect-src' => ["https://heapanalytics.com"],
            'font-src'    => ["https://heapanalytics.com"],
        ];
    }
}
