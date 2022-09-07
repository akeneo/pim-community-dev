<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\IsCertificateValid;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\IsCertificateValidValidator;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsCertificateValidValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsCertificateValidValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_adds_violation_if_identity_provider_certificate_is_invalid(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('This is not a valid certificate.')
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);

        $builder->atPath(Argument::cetera())
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalledTimes(1);

        $command = $this->givenCommand(
            isEnabled: true,
            identityProviderCertificate: $this->givenInvalidCertificate(),
            serviceProviderCertificate: $this->givenValidCertificate()
        );

        $this->validate(
            $command,
            $this->getConstraint()
        );
    }

    function it_adds_violation_if_identity_provider_certificate_is_valid_but_expired(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('This certificate has expired.')
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);

        $builder->atPath(Argument::cetera())
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalledTimes(1);

        $command = $this->givenCommand(
            isEnabled: true,
            identityProviderCertificate: $this->givenValidButExpiredCertificate(),
            serviceProviderCertificate: $this->givenValidCertificate()
        );

        $this->validate(
            $command,
            $this->getConstraint()
        );
    }

    function it_adds_violation_if_service_provider_certificate_is_invalid(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('This is not a valid certificate.')
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);

        $builder->atPath(Argument::cetera())
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalledTimes(1);

        $command = $this->givenCommand(
            isEnabled: true,
            identityProviderCertificate: $this->givenValidCertificate(),
            serviceProviderCertificate: $this->givenInvalidCertificate()
        );

        $this->validate(
            $command,
            $this->getConstraint()
        );
    }

    function it_adds_violation_if_service_provider_certificate_is_valid_but_expired(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('This certificate has expired.')
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);

        $builder->atPath(Argument::cetera())
            ->shouldBeCalledTimes(1)
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalledTimes(1);

        $command = $this->givenCommand(
            isEnabled: true,
            identityProviderCertificate: $this->givenValidCertificate(),
            serviceProviderCertificate: $this->givenValidButExpiredCertificate()
        );

        $this->validate(
            $command,
            $this->getConstraint()
        );
    }

    function it_does_not_check_certificate_validity_when_isEnabled_flag_is_false($context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $command = $this->givenCommand(
            isEnabled: false,
            identityProviderCertificate: $this->givenValidButExpiredCertificate(),
            serviceProviderCertificate: $this->givenValidButExpiredCertificate()
        );

        // This certificate expires in 2028
        $this->validate(
            $command,
            $this->getConstraint()
        );

    }

    private function givenCommand(
        bool $isEnabled,
        string $identityProviderCertificate,
        string $serviceProviderCertificate
    ): CreateOrUpdateConfiguration {
        return new CreateOrUpdateConfiguration(
            code: 'authentication_sso',
            isEnabled: $isEnabled,
            identityProviderEntityId: 'https://idp.jambon.com',
            identityProviderSignOnUrl: 'https://idp.jambon.com/login',
            identityProviderLogoutUrl: 'https://idp.jambon.com/logout',
            identityProviderCertificate: $identityProviderCertificate,
            serviceProviderEntityId: 'https://my-pim.com/saml/metadata',
            serviceProviderCertificate: $serviceProviderCertificate,
            serviceProviderPrivateKey: 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDixA2nuZkTQJau/F77I6j9vhn4lBPhNsANSIg7D1ipbbvpKnFU9heClGeFmzorfl1crkXOgHaPicxJ3otuXDmZv09MkSY5hnyvzU9z6KH5Ntb/s3oYMM8D1tRr7JOBZLy92IC8lsVjdAuZUEV2jFm7K7Fn7htRZg1loJSZP2QboxWJSlHOupiummcR7GDH2X7/R6mchqQDt7OSN1kQ2xNTbK0WHqSpLiwWqbciPmuDYU7R5Cn8imfH37mHZxjz1eXGDjKall4D4snwfIcb7MP2gFORFBBcCdi0NxU3dx9rODLaV84QgGGHG93DlY/z0SgiZ6WTvzJ2HMJ4edyXAgMBAAECggEBAKsZdJguMPLW1Bs6LsxdTsAONPhbelh/AA/Fv4yYerR0KMm4jYSmnnyXTzj/M7fng7hPgjpasZqCRZMjCJ9/lLKOJ96UIzgevktrvFdkGGVi56SVMHX6tLTXfXa4E9hM2mDbCafhh5pQVxNM5LwBQy8vGwpGBGl6YqBAtn6e0VIikrMErR7C65yA6Cp5H2gSCebuRgPbWbVo11AFHyZaD2pB1YrBad0EJ1uvNlyhA32duOytbRw1CGhXmj2QIhonydplCXzih9fGNah3TYlx3QQ8Wc83ZZuciDsQZL0tqKm86gNfrmZFbGErYF//tcSGj/Cc2nCRA/SNiK8/RECgYEA95o5dA7AX2wyi4HtlpjbVMfhmztqehHw1bd7lRU4ErZ27JMiA3kXJe68WxI4jg7s9hmO06e3w5ud3rxmGX69rrui60FvXumG0NW7smZHnElbytugRVgUX7uN6RkoyzUXJLtN0HSRx5CJVEwpE/DcQECvx2rJz6nqv/r4bMnkCgYEA6nTq9rrxHN50L3ggMp/O/rzFBtkDnIzNAqFpX2qkU2PBA0txU9woZtOcGLUzOXRZ2RTmFTXW093h7hr5uBZvL1z7WPGs3mG5L4ZFig/6as6icZ8YT6lGodQJ9VeBk96mPuSBWbY7oC0wzOYEsL0FGe5HEB9dPpvcUbF3TrQ48CgYEAzAuUfUgK0JhhrwYLvaeKWHvAhpdt5C8/tILJsbZdm9kTYDG3UAfiwUtpC7uo9IRS7wrOdOF9m2RRNLVkk6q2/8fhKzWBw8o4LfTwpT9i9tWZh7smgP7tC4HjXodjR/WRrBiI1JixqVoOOKKH62CwN1gV41mXymef1RPCVVEECgYBeJuGMf3oAC91Ais7zRXXMmmXM4C0xGuHhIoy8QokG69JAznUOJiUbVfMjgPC3KBA6sGS1vIUVtA53B9YK7ou1wLLyq9TJkg33oddVLIit/5McbNbZ/lwzEQ/BT0/Y3OXwtWXoQWrwLENqDcjoaJ50BQ9hzrcv550N/4fXwRQKBgH4VLrgH/oJ94SmipDRaKoeBXBdVYgwrtFBh8txrRADZX8JuGKIIxReRRwdx5DhAn5Y99gwIpvCjt5F6EteSf9FN9Jqm4aFmFokBk8KGewr3rADbXwUldkPV7I0jdZuEPm4I0NpFdKk6uw0oQ3ozz6sbpBowuJD8iW3cCxD/tiXT'
        );
    }

    private function givenInvalidCertificate(): string
    {
        return 'invalidCertificate';
    }

    private function givenValidCertificate(): string
    {
        // This certificate expires in 2072
        return 'MIIDbTCCAlWgAwIBAgIURYY8Ycw0oEL8DctQcc03m9XGVHIwDQYJKoZIhvcNAQELBQAwRTELMAkGA1UEBhMCQVUxEzARBgNVBAgMClNvbWUtU3RhdGUxITAfBgNVBAoMGEludGVybmV0IFdpZGdpdHMgUHR5IEx0ZDAgFw0yMjA5MDYwODQwMzdaGA8yMDcyMDgyNDA4NDAzN1owRTELMAkGA1UEBhMCQVUxEzARBgNVBAgMClNvbWUtU3RhdGUxITAfBgNVBAoMGEludGVybmV0IFdpZGdpdHMgUHR5IEx0ZDCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAOGFe5LrhtAns/PJsZvO/3+8UenIhxC60U2Vcd5F7OvruYGxffdg4NXcSAl9TZjPwLuGExqqdtvPC8yRFt2N+UNKmFQyLe9VQNrIKCxf4gnAaFVxzoDjDzmbTbjnQ1N5AIQNhDE7011jcpBZlL+hh9pVySbbU1jcksHodzMauMD3769dNv5fzU7ieV3JAAzUDnT2fkzmAq7qq6DJhdpcJVuMGtuwzEDkvODHmoBuYjZNlypsNISDmwVRIHxNFwD2m4iUc110gNj/4cy+Jr/F/2Dc14SCYdRMh6tOMtaivqoyzb7tePj5qLKQPXbh+0RLlwdiFEoQi0florAxfT0xyAECAwEAAaNTMFEwHQYDVR0OBBYEFL4bOjBD3za8dHaqysR17MiFwgMnMB8GA1UdIwQYMBaAFL4bOjBD3za8dHaqysR17MiFwgMnMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBAAqpEpp7K2cgTY7kXKlQNlbRts49jB2bEkSgZ6Bb6ukJKBiJklffQUJHjnGFlSwathZe2d52B/t/ylgYkToLLrp1W6jknuJOqXrJ2bBvVj3Y/T4KfPxrIlcPNhbjzdsP5VXRCnB+benQldbMKFyOET77GD20NnLuCec5NrOtlg2qxCiLQPh+pFVJo8BKQcekMzX2KeyOGl3990L5kZzENM85lKJgFo+ceXWUlSlHUhUUTgkc3QkDxsWrJ7tUW5Lx9f8I4eL9PbU7lyxfR3Q13R7NmBl4U1Ft7GhmALd52G01FxTRL/aVY8+IQWO8Q7P8/7L/TT5XIUihU9WI2SLDb74=';
    }

    private function givenValidButExpiredCertificate(): string
    {
        return 'MIIDazCCAlOgAwIBAgIUUBZUrf9V7zLC9H0E1jSI3L3aMhQwDQYJKoZIhvcNAQELBQAwRTELMAkGA1UEBhMCQVUxEzARBgNVBAgMClNvbWUtU3RhdGUxITAfBgNVBAoMGEludGVybmV0IFdpZGdpdHMgUHR5IEx0ZDAeFw0yMjA5MDIxNTAwMDRaFw0yMjA5MDMxNTAwMDRaMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDgWJzhlUWrJ1JhVvjyX9zFSF2DBHe4wLNix4JBgV25Br/Sihy8R0SXvLWTle1mDl2ojTKDO99hmVwjxBWIILhaYLcCTTjk3Ho+msf7iWYB5u3bIi3zMV3QRoeAzvTwwZqUv/H9Y7fJXgTXkjMOoqqLdhaSAnRrCDkXa5y3xAzdcGby1Mr+WyvFtzlonmEaHyHoktx1hRxIDa1Y4Uy3kRg8xw8xAhK7vJmyGXkwXDu4IDnMJxQJ1oltaYehCvpvDmp/LuQRczwSHJ/aDRcfQbY5O5A3QK6PLa9a2wEG3hRakBfzHvikIbYCf7sHpqBI/P5RqILEeRkP40zuUqProzOlAgMBAAGjUzBRMB0GA1UdDgQWBBQcHYC9imVutZ0JaPJ2VWKOV3PvYjAfBgNVHSMEGDAWgBQcHYC9imVutZ0JaPJ2VWKOV3PvYjAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQCV98D7U8Qg5sB4TPArtkJE5sZmthsOj9VJqSSrm7BHtaF3LvvqHod7D++Lmd+HqIm+ZAmn/x5soPX5T8X+ep8lA4SrDkgJ208OEkdimHyJnnVnWvjADNmI+Otf78pl3l0crG/yreak4pIY8379gNodhDRTFLOFY39yCVcKi6rRBZ/AhxQhfQMyTvKUYboKxn7mZ4ClYNH3++eLUa4mFcJkW/bMw7HUQzLLrPhqX+8Mr45FwwdmBxTAsTmicaVVPYz8TV60WmZaAimbeTW1RIZcWrY5T/LcPA9pVwyixYaub51HuYGUlGKu8lTr8n9UIKw2a9BPe0DOaTvQ7SpedcZ/';
    }

    private function getConstraint(): IsCertificateValid
    {
        return new IsCertificateValid(
            [
                'invalidMessage' => 'This is not a valid certificate.',
                'expiredMessage' => 'This certificate has expired.',
                'identityProviderCertificate' => 'identityProviderCertificate',
                'serviceProviderCertificate' => 'serviceProviderCertificate',
            ]
        );
    }
}
