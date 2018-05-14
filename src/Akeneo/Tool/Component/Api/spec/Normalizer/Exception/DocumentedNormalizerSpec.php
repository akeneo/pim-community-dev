<?php

namespace spec\Akeneo\Tool\Component\Api\Normalizer\Exception;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Symfony\Component\HttpFoundation\Response;

class DocumentedNormalizerSpec extends ObjectBehavior
{
    function it_normalizes_an_exception()
    {
        $exception = new DocumentedHttpException(
            'http://example.net',
            'Property "xx" does not exist'
        );

        $this->normalize($exception)->shouldReturn([
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Property "xx" does not exist',
            '_links' => [
                'documentation' => [
                    'href' => 'http://example.net'
                ]
            ]
        ]);
    }
}
