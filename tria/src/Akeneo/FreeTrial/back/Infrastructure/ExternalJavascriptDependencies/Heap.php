<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\ExternalJavascriptDependencies;

use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;
use Akeneo\Platform\Bundle\UIBundle\Provider\ContentSecurityPolicy\ContentSecurityPolicyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\ExternalDependencyProviderInterface;

final class Heap implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    private ScriptNonceGenerator $nonceGenerator;

    private string $heapId;

    public function __construct(ScriptNonceGenerator $nonceGenerator, string $heapId)
    {
        $this->nonceGenerator = $nonceGenerator;
        $this->heapId = $heapId;
    }

    public function getScript(): string
    {
        $nonce = $this->nonceGenerator->getGeneratedNonce();

        $javascript = <<<JS
<script type="text/javascript" nonce="$nonce">
    window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=document.createElement("script");r.type="text/javascript",r.async=!0,r.src="https://cdn.heapanalytics.com/js/heap-"+e+".js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(r,a);for(var n=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","resetIdentity","removeEventProperty","setEventProperties","track","unsetEventProperty"],o=0;o<p.length;o++)heap[p[o]]=n(p[o])};
    heap.load("%s");
</script>
JS;

        return sprintf($javascript, $this->heapId);
    }

    /**
     * https://developers.heap.io/docs/web#content-security-policy-csp
     */
    public function getContentSecurityPolicy(): array
    {
        return [
            'script-src'  => ["https://cdn.heapanalytics.com", "https://heapanalytics.com", "'unsafe-inline'", "'unsafe-eval'"],
            'img-src'     => ["https://heapanalytics.com", "https://logo.clearbit.com"],
            'style-src'   => ["https://heapanalytics.com"],
            'connect-src' => ["https://heapanalytics.com"],
            'font-src'    => ["https://heapanalytics.com"],
        ];
    }
}
