<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Normalizer;

use Akeneo\AssetManager\Domain\Exception\UserFacingError;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorFacingNormalizerSpec extends ObjectBehavior
{
    public function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }
    public function it_normalizes_an_error_facing_error(
        TranslatorInterface $translator,
        UserFacingError $error
    ) {
        $translator->trans('a_translation_id', ['parameter' => 'value'], 'validators')->willReturn('A translation');
        $error->translationId()->willReturn('a_translation_id');
        $error->translationParameters()->willReturn([
            'parameter' => 'value'
        ]);

        $this->normalize($error, '[code]')->shouldReturn(
            [
                [
                    'messageTemplate' => 'a_translation_id',
                    'parameters' => ['parameter' => 'value'],
                    'message' => 'A translation',
                    'propertyPath' => '[code]',
                ],
            ]
        );

    }
}
