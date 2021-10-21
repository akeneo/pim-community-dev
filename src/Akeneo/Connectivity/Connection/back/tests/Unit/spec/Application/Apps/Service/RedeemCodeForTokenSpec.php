<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Service\RedeemCodeForToken;
use Akeneo\Connectivity\Connection\Application\Apps\Service\RedeemCodeForTokenInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RedeemCodeForTokenSpec extends ObjectBehavior
{
    public function let(ValidatorInterface $validator): void
    {
        $this->beConstructedWith($validator);
    }

    public function it_is_a_redeem_code_for_token(): void
    {
        $this->shouldHaveType(RedeemCodeForToken::class);
        $this->shouldImplement(RedeemCodeForTokenInterface::class);
    }

    public function it_redeems_a_code_for_a_token(ValidatorInterface $validator): void
    {
        $accessTokenRequest = new AccessTokenRequest('',
            '',
            'authorization_code',
            '1234',
            '1234'
        );
        $violations = new ConstraintViolationList([]);
        $validator->validate($accessTokenRequest)->willReturn($violations);

        $this->redeem($accessTokenRequest)->shouldReturn('a_random_token');
    }

    public function it_does_not_accept_to_process_invalid_argument(ValidatorInterface $validator): void
    {
        $accessTokenRequest = new AccessTokenRequest('',
            '',
            'authorization_code',
            '1234',
            '1234'
        );

        $violations = new ConstraintViolationList([
            new ConstraintViolation('invalid_request client_id', 'not_blank', [], 'not_blank', 'clientId', ''),
            new ConstraintViolation('invalid_request authorization_code', 'not_blank', [], 'not_blank', 'authorizationCode', ''),
        ]);
        $validator->validate($accessTokenRequest)->willReturn($violations);

        $this
            ->shouldThrow(
                new \InvalidArgumentException('invalid_request client_id')
            )
            ->during('redeem', [$accessTokenRequest]);
    }
}
