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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\ExternalDependencies;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\ContentSecurityPolicyProviderInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\POC\ExternalDependencyProviderInterface;
use Akeneo\Platform\Bundle\UIBundle\EventListener\ScriptNonceGenerator;

final class Heap implements ExternalDependencyProviderInterface, ContentSecurityPolicyProviderInterface
{
    private ScriptNonceGenerator $nonceGenerator;

    public function __construct(ScriptNonceGenerator $nonceGenerator)
    {
        $this->nonceGenerator = $nonceGenerator;
    }

    public function getScript(): string
    {
        $nonce = $this->nonceGenerator->getGeneratedNonce();
        return <<<JS
<script type="text/javascript" nonce="$nonce">
    window.heap=window.heap||[],heap.load=function(e,t){window.heap.appid=e,window.heap.config=t=t||{};var r=document.createElement("script");r.type="text/javascript",r.async=!0,r.src="https://cdn.heapanalytics.com/js/heap-"+e+".js";var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(r,a);for(var n=function(e){return function(){heap.push([e].concat(Array.prototype.slice.call(arguments,0)))}},p=["addEventProperties","addUserProperties","clearEventProperties","identify","resetIdentity","removeEventProperty","setEventProperties","track","unsetEventProperty"],o=0;o<p.length;o++)heap[p[o]]=n(p[o])};
    heap.load("2875170433");
</script>
JS;

    }

    public function getContentSecurityPolicy(): array
    {
        return [
            'script-src'  => ["https://cdn.heapanalytics.com", "https://heapanalytics.com", "'unsafe-inline'", "'unsafe-eval'"],
            'img-src'     => ["https://heapanalytics.com"],
            'style-src'   => ["https://heapanalytics.com"],
            'connect-src' => ["https://heapanalytics.com"],
            'font-src'    => ["https://heapanalytics.com"],
        ];
    }
}
