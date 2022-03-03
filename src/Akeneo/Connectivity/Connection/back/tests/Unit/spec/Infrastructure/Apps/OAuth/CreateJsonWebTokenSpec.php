<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAsymmetricKeysQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\CreateJsonWebToken;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateJsonWebTokenSpec extends ObjectBehavior
{
    private \DateTimeImmutable $now;
    private string $pimUrl;
    private string $privateKey;
    private string $publicKey;
    private string $clientId;
    private string $ppid;
    private string $firstname;
    private string $lastname;
    private string $email;

    public function let(GetAsymmetricKeysQueryInterface $getAsymmetricKeysQuery, ClockInterface $clock, PimUrl $pimUrl): void
    {
        $this->setCommonData();
        $getAsymmetricKeysQuery->execute()->willReturn(AsymmetricKeys::create($this->publicKey, $this->privateKey));
        $clock->now()->willReturn($this->now);
        $pimUrl->getPimUrl()->willReturn($this->pimUrl);

        $this->beConstructedWith($clock, $pimUrl, $getAsymmetricKeysQuery);
    }

    public function it_is_a_crate_json_web_token(): void
    {
        $this->shouldBeAnInstanceOf(CreateJsonWebToken::class);
    }

    public function it_creates_jwt_token(): void
    {
        $token = $this->create(
            $this->clientId,
            $this->ppid,
            ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID]),
            $this->firstname,
            $this->lastname,
            $this->email
        );

        $this->assertToken($token->getWrappedObject());
    }

    public function it_creates_jwt_token_with_scope_profile(): void
    {
        $token = $this->create(
            $this->clientId,
            $this->ppid,
            ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID, AuthenticationScope::SCOPE_PROFILE]),
            $this->firstname,
            $this->lastname,
            $this->email
        );

        $this->assertToken($token->getWrappedObject(), [AuthenticationScope::SCOPE_PROFILE]);
    }

    public function it_creates_jwt_token_with_scope_email(): void
    {
        $token = $this->create(
            $this->clientId,
            $this->ppid,
            ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID, AuthenticationScope::SCOPE_EMAIL]),
            $this->firstname,
            $this->lastname,
            $this->email
        );

        $this->assertToken($token->getWrappedObject(), [AuthenticationScope::SCOPE_EMAIL]);
    }

    public function it_throws_exception_because_openid_scope_has_not_been_consented(): void
    {
        $this->shouldThrow(\LogicException::class)->during('create', [
            $this->clientId,
            $this->ppid,
            ScopeList::fromScopes([AuthenticationScope::SCOPE_PROFILE, AuthenticationScope::SCOPE_EMAIL]),
            $this->firstname,
            $this->lastname,
            $this->email,
        ]);
    }

    private function assertToken(string $tokenToCheck, array $scopes = []): void
    {
        $privateKey = InMemory::plainText($this->privateKey);
        $publicKey = InMemory::plainText($this->publicKey);
        $configuration = Configuration::forAsymmetricSigner(new Sha256(), $privateKey, $publicKey);
        $token = $configuration->parser()->parse($tokenToCheck);

        Assert::assertInstanceOf(UnencryptedToken::class, $token);

        $configuration->setValidationConstraints(new IssuedBy($this->pimUrl));
        $configuration->setValidationConstraints(new RelatedTo($this->ppid));
        $configuration->setValidationConstraints(new PermittedFor($this->clientId));
        $configuration->setValidationConstraints(new LooseValidAt(new FrozenClock($this->now)));
        $configuration->setValidationConstraints(
            new SignedWith($configuration->signer(), $configuration->verificationKey())
        );

        Assert::assertTrue($configuration->validator()->validate($token, ...$configuration->validationConstraints()));

        Assert::assertTrue($token->claims()->has(RegisteredClaims::ISSUED_AT));
        $ia = $token->claims()->get(RegisteredClaims::ISSUED_AT);
        Assert::assertInstanceOf(\DateTimeInterface::class, $ia);
        Assert::assertEquals($this->now->format(\DateTimeInterface::ATOM), $ia->format(\DateTimeInterface::ATOM));

        Assert::assertTrue($token->claims()->has(RegisteredClaims::EXPIRATION_TIME));
        $et = $token->claims()->get(RegisteredClaims::EXPIRATION_TIME);
        $expectedEt = $this->now->add(new \DateInterval('PT1H'));
        Assert::assertInstanceOf(\DateTimeInterface::class, $et);
        Assert::assertEquals($expectedEt->format(\DateTimeInterface::ATOM), $et->format(\DateTimeInterface::ATOM));

        if (\in_array(AuthenticationScope::SCOPE_PROFILE, $scopes)) {
            Assert::assertTrue($token->claims()->has('firstname'));
            Assert::assertTrue($token->claims()->has('lastname'));
            Assert::assertEquals($this->firstname, $token->claims()->get('firstname'));
            Assert::assertEquals($this->lastname, $token->claims()->get('lastname'));
        }

        if (\in_array(AuthenticationScope::SCOPE_EMAIL, $scopes)) {
            Assert::assertTrue($token->claims()->has('email'));
            Assert::assertEquals($this->email, $token->claims()->get('email'));
        }
    }

    private function setCommonData(): void
    {
        $this->now = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2021-12-12T00:00:00Z');
        $this->pimUrl = 'http://my-akeneo.test';
        $this->clientId = 'a_client_id';
        $this->ppid = 'a_ppid';
        $this->firstname = 'a_first_name';
        $this->lastname = 'a_last_name';
        $this->email = 'an_email';

        $this->privateKey = <<<EOD
        -----BEGIN RSA PRIVATE KEY-----
        MIICXAIBAAKBgQC8kGa1pSjbSYZVebtTRBLxBz5H4i2p/llLCrEeQhta5kaQu/Rn
        vuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t0tyazyZ8JXw+KgXTxldMPEL9
        5+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4ehde/zUxo6UvS7UrBQIDAQAB
        AoGAb/MXV46XxCFRxNuB8LyAtmLDgi/xRnTAlMHjSACddwkyKem8//8eZtw9fzxz
        bWZ/1/doQOuHBGYZU8aDzzj59FZ78dyzNFoF91hbvZKkg+6wGyd/LrGVEB+Xre0J
        Nil0GReM2AHDNZUYRv+HYJPIOrB0CRczLQsgFJ8K6aAD6F0CQQDzbpjYdx10qgK1
        cP59UHiHjPZYC0loEsk7s+hUmT3QHerAQJMZWC11Qrn2N+ybwwNblDKv+s5qgMQ5
        5tNoQ9IfAkEAxkyffU6ythpg/H0Ixe1I2rd0GbF05biIzO/i77Det3n4YsJVlDck
        ZkcvY3SK2iRIL4c9yY6hlIhs+K9wXTtGWwJBAO9Dskl48mO7woPR9uD22jDpNSwe
        k90OMepTjzSvlhjbfuPN1IdhqvSJTDychRwn1kIJ7LQZgQ8fVz9OCFZ/6qMCQGOb
        qaGwHmUK6xzpUbbacnYrIM6nLSkXgOAwv7XXCojvY614ILTK3iXiLBOxPu5Eu13k
        eUz9sHyD6vkgZzjtxXECQAkp4Xerf5TGfQXGXhxIX52yH+N2LtujCdkQZjXAsGdm
        B2zNzvrlgRmgBrklMTrMYgm1NPcW+bRLGcwgW2PTvNM=
        -----END RSA PRIVATE KEY-----
        EOD;

        $this->publicKey = <<<EOD
        -----BEGIN PUBLIC KEY-----
        MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC8kGa1pSjbSYZVebtTRBLxBz5H
        4i2p/llLCrEeQhta5kaQu/RnvuER4W8oDH3+3iuIYW4VQAzyqFpwuzjkDI+17t5t
        0tyazyZ8JXw+KgXTxldMPEL95+qVhgXvwtihXC1c5oGbRlEDvDF6Sa53rcFVsYJ4
        ehde/zUxo6UvS7UrBQIDAQAB
        -----END PUBLIC KEY-----
        EOD;
    }
}
