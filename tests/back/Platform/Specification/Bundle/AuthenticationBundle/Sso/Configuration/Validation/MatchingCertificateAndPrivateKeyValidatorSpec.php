<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\MatchingCertificateAndPrivateKey;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\MatchingCertificateAndPrivateKeyValidator;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MatchingCertificateAndPrivateKeyValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MatchingCertificateAndPrivateKeyValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_passes_if_the_certificates_match($context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $command = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            'https://idp.jambon.com',
            'https://idp.jambon.com/login',
            'https://idp.jambon.com/logout',
            'MIIDYDCCAkigAwIBAgIJAOGDWOB07tCyMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwHhcNMTgwOTE0MDkzMDEzWhcNMjgwOTEzMDkzMDEzWjBFMQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4J5iDNmrQLn4NHVvjTR0ZxqmW6mYWFP/MxI4D4urwv6J0CLZppxfcSXLYogxrC5UJxlF7jv9CM6Dpvkc4xBFyCNVIKAwBh/WfL85m48Fd7Nh1VWfK8ZBDUKFfuRxKH/0shU96z2onVB6uYiNxF026MwZwjecLIh6stpEKzd2aUNgB9RYPJWqdxw8R5mZH2EfzjTDKyomAeENcVW6zK9kQP6YNC7T8mYaUus4jhAcC/jV8Iqy7Oc1htEQV3rqFLLKezuNZWufoOrzaPoKMOkXgasxtadM1wU9InIpiO6pWPCwNc6TLpmZCcry6yIoveMx5fzMGjgxmmmUrwIDAQABo1MwUTAdBgNVHQ4EFgQUitGGamyDFTInis6UmdWcNoFoMwHwYDVR0jBBgwFoAUitGGamyDFTInis6UmdWcNoFoMwDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEA05pnfsR6Atm9WxfdaFG7DVnrQjLDXRCXqkRJ09ygrpJlIF6YBPXcYA0kidoVlBbkZhWzz1ht5i1BYKzKdWzB2BQZLUnkaM8UotKFjdHu7/7vnM7w/n3S5zx3gtoCMSegp9vk6H2wjsPYfR0mVJOYcFzRY48bdQLV6nJRU3gVZikM/u92xArcaTCS6l4YEBCqJWtvlVojc6nwwv262t6NJ8NHRHqV98aoNMO4ltjFIkXa0xtNqYo7pI01kkTlPrignb4djZjCpdwu/lZJTy4FAra4lTdu2j4nn8QxNKDoBIrsNx6bC767Mtf1f3JSRMAvt/IE4Wjp5IIAeLsTHSA==',
            'https://my-pim.com/saml/metadata',
            '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'
        );
        
        $this->validate(
            $command,
            $this->getConstraint()
        );
    }

    function it_adds_violations_if_the_certificates_do_not_match(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {

        $context->buildViolation('Service Provider certificate and private key must match.')
            ->shouldBeCalledTimes(2)
            ->willReturn($builder)
        ;

        $builder->atPath(Argument::cetera())->shouldBeCalledTimes(2)->willReturn($builder);
        $builder->addViolation()->shouldBeCalledTimes(2);

        $command = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            'https://idp.jambon.com',
            'https://idp.jambon.com/login',
            'https://idp.jambon.com/logout',
            'MIIDYDCCAkigAwIBAgIJAOGDWOB07tCyMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwHhcNMTgwOTE0MDkzMDEzWhcNMjgwOTEzMDkzMDEzWjBFMQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4J5iDNmrQLn4NHVvjTR0ZxqmW6mYWFP/MxI4D4urwv6J0CLZppxfcSXLYogxrC5UJxlF7jv9CM6Dpvkc4xBFyCNVIKAwBh/WfL85m48Fd7Nh1VWfK8ZBDUKFfuRxKH/0shU96z2onVB6uYiNxF026MwZwjecLIh6stpEKzd2aUNgB9RYPJWqdxw8R5mZH2EfzjTDKyomAeENcVW6zK9kQP6YNC7T8mYaUus4jhAcC/jV8Iqy7Oc1htEQV3rqFLLKezuNZWufoOrzaPoKMOkXgasxtadM1wU9InIpiO6pWPCwNc6TLpmZCcry6yIoveMx5fzMGjgxmmmUrwIDAQABo1MwUTAdBgNVHQ4EFgQUitGGamyDFTInis6UmdWcNoFoMwHwYDVR0jBBgwFoAUitGGamyDFTInis6UmdWcNoFoMwDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEA05pnfsR6Atm9WxfdaFG7DVnrQjLDXRCXqkRJ09ygrpJlIF6YBPXcYA0kidoVlBbkZhWzz1ht5i1BYKzKdWzB2BQZLUnkaM8UotKFjdHu7/7vnM7w/n3S5zx3gtoCMSegp9vk6H2wjsPYfR0mVJOYcFzRY48bdQLV6nJRU3gVZikM/u92xArcaTCS6l4YEBCqJWtvlVojc6nwwv262t6NJ8NHRHqV98aoNMO4ltjFIkXa0xtNqYo7pI01kkTlPrignb4djZjCpdwu/lZJTy4FAra4lTdu2j4nn8QxNKDoBIrsNx6bC767Mtf1f3JSRMAvt/IE4Wjp5IIAeLsTHSA==',
            'https://my-pim.com/saml/metadata',
            'MIICBzCCAXCgAwIBAgIUDudH1bB0oQRaChzLtV5/FFpAoM4wDQYJKoZIhvcNAQEFBQAwQDE+MDwGA1UECgw1YXV0b2dlbmVyYXRlZCBzZWxmLXNpZ25lZCBjZXJ0aWZpY2F0IHdpdGhvdXQgZW5kIGRhdGUwHhcNMTkwMTAzMDkxNzA1WhcNMjAwMTAzMDkxNzA1WjBAMT4wPAYDVQQKDDVhdXRvZ2VuZXJhdGVkIHNlbGYtc2lnbmVkIGNlcnRpZmljYXQgd2l0aG91dCBlbmQgZGF0ZTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEA37Z3eVNGi0syo0z5wt9tdumv9hlStD6upPzilbW9gg2LAvPAdKEiIP4by5zl5dZMTwlpxwFgHaNlH6C+ec5nvZpTyVp+67oz03aY9/unmxB84bgkzSlIyJd7UeTv4c0tiJB7ULWZUKhiGgysApfPmo+1jF1A64KRLKIdBf7KwQUCAwEAATANBgkqhkiG9w0BAQUFAAOBgQBlqqnPIy4Xm+Cm6a+w7CRzcsLWTzFUflEdu/w21Hki7xTqRNwa463gXzCjlPSmKayRD59HIuC+bAI/M14nQWWNNwVZvx4zgNANloRBsGfm3lcxPe3nAwtglCW4YKzJHy5ixexclKj0C58zHMlrLy+W0CpP7nuMeDMWw4Wvp69YBg==',
            'InvalidCertificate'
        );

        $this->validate(
            $command,
            $this->getConstraint()
        );
    }

    private function getConstraint(): MatchingCertificateAndPrivateKey
    {
        return new MatchingCertificateAndPrivateKey(
            [
                'message'                 => 'Service Provider certificate and private key must match.',
                'certificatePropertyName' => 'serviceProviderCertificate',
                'privateKeyPropertyName'  => 'serviceProviderPrivateKey',
            ]
        );      
    }
}
