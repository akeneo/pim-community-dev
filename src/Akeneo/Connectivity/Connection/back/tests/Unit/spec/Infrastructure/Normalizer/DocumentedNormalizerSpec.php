<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Connectivity\Connection\Infrastructure\Normalizer\DocumentedNormalizer;
use Akeneo\Pim\Enrichment\Component\DocumentedExceptionInterface;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DocumentedNormalizerSpec extends ObjectBehavior
{
    public function it_is_a_normalizer(): void
    {
        $this->shouldHaveType(DocumentedNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(CacheableSupportsMethodInterface::class);
    }

    public function it_is_cacheable(): void
    {
        $this->hasCacheableSupportsMethod()->shouldReturn(true);
    }

    public function it_supports_documented_exception(): void
    {
        $previousException = new DocumentedException();
        $exception = new DocumentedHttpException('_link', 'message', $previousException, 422);

        $this->supportsNormalization($exception)->shouldReturn(true);
    }

    public function it_normalizes_a_documented_exception(): void
    {

        $previousException = new DocumentedException();
        $exception = new DocumentedHttpException('_link', 'message', $previousException, 422);

        $this->normalize($exception)->shouldReturn(
            [
                'code' => 422,
                'message' => 'message',
                '_links' =>  [
                    'documentation' => ['href' => '_link']
                ],
                'documentation' => $previousException->getDocumentation()
            ]
        );
    }

    public function it_supports_only_documented_exception(): void
    {
        $previousException = new \Exception();
        $exception = new \Exception('message', 422, $previousException);

        $this->supportsNormalization($exception)->shouldReturn(false);
    }

    public function it_is_able_to_normalize_only_supported_data(): void
    {
        $previousException = new \Exception();
        $exception = new \Exception('message', 422, $previousException);

        $this->shouldThrow(\InvalidArgumentException::class)->during('normalize', [$exception]);
    }

}

class DocumentedException extends \Exception implements DocumentedExceptionInterface
{
    public function getDocumentation(): array
    {
        return [
            [
                'message' => 'my message %s',
                'params' => [
                    [
                        'route' => 'pim_enrich_attribute_index',
                        'params' => [],
                        'title' => 'Attributes settings',
                        'type' => 'route',
                    ],
                ],
            ]
        ];
    }
}
