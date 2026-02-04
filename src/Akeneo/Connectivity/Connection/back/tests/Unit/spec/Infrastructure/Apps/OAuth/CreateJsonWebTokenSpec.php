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

    public function it_is_a_create_json_web_token(): void
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

        $configuration->setValidationConstraints(
            new IssuedBy($this->pimUrl),
            new RelatedTo($this->ppid),
            new PermittedFor($this->clientId),
            new LooseValidAt(new FrozenClock($this->now)),
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
        MIIEowIBAAKCAQEAxMV/ALXjkbQp50M26P9op95v1LIHs3vLvjROGxEBBnR/dEYN
        HVbmOxKRGL8yne8xow7W4NiMVvm2VdgoDyTKF48cU679W7rsfkJqK0u1vGitlH0z
        wG2e3AoMw+/7yLjCotG/PgRNSzXa6W2dTqduPO/2UeRBrScEpR6xnGQDrY5hXckS
        Y1JU36laadlSYHIE3sf/VLoKItHeXHjeaXRGb0xGJ5pn1t33HfyI0ASSjURzANzB
        jok/5kHq4g2xlkH1xSRSlgRyVhx8+5Pkuhe2mxDs1tJhaibZOxEb8kP4fizfxipg
        lLEHclMdqDxoznxrI1XKTAfiAjhHlRxUa4B4dQIDAQABAoIBAEf70/1HjQvVc+rl
        XOYZ4YhfyFtwEX8oj51ydwxRySU6YxH/Onb8Pldn8Gq0L2k1gtwa5qL0tUpwKbL3
        05fOppu9v+ghQRBYroF1/G8AUGivhqimsNL5hz8J8ieP2HVSmemEf8jJPBmChyYT
        8pM+jwZ95oeI0Dnu5zUcqG8E64+GvFYtT7Q99+U2yk/M/fA+tJP7fTWQgWSNm4MB
        +oP4sFZMKV/qE3OWVHTWQ881TjmMpkdiyCwgUL3BZK4z/DmHoKW7KZr5DIFH+7MF
        gGXHv59TJIGM2fyc6/CYaJ0znvEHZhjyYLgHWIVQPqeqXiVk21E8QfpukPtwnkcu
        rSWaQyECgYEA/PM+4Ka9itSX7LWj2tDEGg5hGyJTA00AjXcZLYMpKRbAkDwT/5Aw
        bwlV00//X3az0bfS1YuUziTXQ7qgWL4YFSFrxwTNceyDYiDMNPfa7ffjvO6SpPJ5
        HWTXtpuseNrZHBkz2JkOSPkx/BQUYsz/RwMQdOzQpbPdCSo76HO+Gq0CgYEAxyTZ
        gpq0Ppmb95ypPQikI2Ylgl3Mzs7ou1Cgf+ZVfXppDdRWt2bwEzid8kBsE1AGA0Zm
        k+u2/ALDZDKp6uZ+qEHJRhBm1T/U/zA7c19x6YC7g2D33nMvLLeFlx9MuHKUV5+W
        31gi//E8VqizPhPSk8LopeLCo+8X7GHVQouyFekCgYBatKtuibxcZWHZa0VHuScp
        JNDjlwpnm5xAHl4z+N2ws0z4K+ML+Nu1ZYaWURCFXh6bbKy5EOWaipF64xiO2hPu
        t95bLrixSpvOe25e7CZgwUy0OmTxq1WNGdVU0Twm1muWbN8vo6sAtgObnmO1DkfY
        YhvroeQsF3SCzddPwvl/vQKBgFiKR7LLuavDfBbBLnWWa/PZLIAj2DVyxQLTPCjh
        bc0WKbMeX1e3irHhEEhu4B5OC/5UxLKrsHWnfNwFsopf5JxGc4iVLkNN2BOFjEkl
        fG4G8FffOxVKPQUyq1Cfd+rh9pZmvBudAiKtTNhytQ66nXtYwztN8KAWY5qTfM/T
        cGBRAoGBAMrb6qicrZjjlsyx1Ci5AUE9jIE913R3G0Ner2EUVxrbDoNhD/AoCIuG
        uJkuKNYJY20BSf4l7gwfZFA9uGBHpkhJZACq7vRoCKm0jOyhmmuoRZ/ClQZi8dq1
        f8h9KC7ZDrEnth5za44DZYPXKcAxxsi7Zv6nmQO7qys0a1F2v4Pn
        -----END RSA PRIVATE KEY-----
        EOD;

        $this->publicKey = <<<EOD
        -----BEGIN CERTIFICATE-----
        MIIC7zCCAdegAwIBAgIUf9G4IrNSbjiOJW17UepkNlkh64owDQYJKoZIhvcNAQEL
        BQAwETEPMA0GA1UECgwGQWtlbmVvMB4XDTIyMTIxMzE0NTgxN1oXDTIzMTIxMzE0
        NTgxN1owETEPMA0GA1UECgwGQWtlbmVvMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A
        MIIBCgKCAQEAxMV/ALXjkbQp50M26P9op95v1LIHs3vLvjROGxEBBnR/dEYNHVbm
        OxKRGL8yne8xow7W4NiMVvm2VdgoDyTKF48cU679W7rsfkJqK0u1vGitlH0zwG2e
        3AoMw+/7yLjCotG/PgRNSzXa6W2dTqduPO/2UeRBrScEpR6xnGQDrY5hXckSY1JU
        36laadlSYHIE3sf/VLoKItHeXHjeaXRGb0xGJ5pn1t33HfyI0ASSjURzANzBjok/
        5kHq4g2xlkH1xSRSlgRyVhx8+5Pkuhe2mxDs1tJhaibZOxEb8kP4fizfxipglLEH
        clMdqDxoznxrI1XKTAfiAjhHlRxUa4B4dQIDAQABoz8wPTALBgNVHQ8EBAMCAQYw
        DwYDVR0TAQH/BAUwAwEB/zAdBgNVHQ4EFgQUuajENl2pPtXRJg2trCIV4u/qj1ow
        DQYJKoZIhvcNAQELBQADggEBAGggphqPc72aqeaYOhu+oCqDmWymiiZvPAf8y+AY
        cP1P4E4xcxc+xtqZOFfb9wtv2shjMf1Bz4XezxngssnNZr1PrmBuO+/am/4Q9KLl
        WJV7qKEn3MZCx8Ajpw1XPkAu/ptqylm4dM8qiGBZbj94k4MpIGFhRIaPE1ii+Mz9
        lmH84Y9kMHpg2tGW0hD9covc20BSii2TzkHlohfk6u0vXjkubXyq4VkwkRn2Rvh4
        QqpHwbLe9lru+mlcn5HMmc4rVxE2q3BUhY8caZ73B/Vyejsfvuslu0j22xTSEjcR
        1L/iY6KJdPVRR+6GZd4DMP+TChLM1U+jwxvfZNTSHNawBVo=
        -----END CERTIFICATE-----
        EOD;
    }
}
