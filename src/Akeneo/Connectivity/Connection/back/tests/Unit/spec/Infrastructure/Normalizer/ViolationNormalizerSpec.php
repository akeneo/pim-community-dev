<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Connectivity\Connection\Infrastructure\Normalizer\ViolationNormalizer;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ViolationNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ViolationNormalizer::class);
    }

    public function it_is_cacheable(): void
    {
        $this->hasCacheableSupportsMethod()->shouldReturn(true);
    }

    public function it_supports_violation_http_exception(): void
    {
        $exception = new ViolationHttpException(new ConstraintViolationList());

        $this->supportsNormalization($exception)->shouldReturn(true);
    }

    public function it_supports_only_violation_http_exception(): void
    {
        $exception = new \Exception();

        $this->supportsNormalization($exception)->shouldReturn(false);
    }

    public function it_normalizes_a_violation_http_exception(): void
    {
        $exception = new ViolationHttpException(
            new ConstraintViolationList(),
            'message'
        );

        $this->normalize($exception)->shouldReturn(
            [
                'code' => 422,
                'message' => 'message',
                'errors' =>  [
                ],
                'raw_message' => '',
                'parameters' => '',
                'type' => 'violation',
            ]
        );
    }
}
