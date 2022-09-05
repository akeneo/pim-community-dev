<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\Application\CreateOrUpdateConfiguration;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\IsCertificateValid;
use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation\IsCertificateValidValidator;
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

    function it_passes_if_the_certificate_is_valid($context)
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

        // This certificate expires in 2028
        $this->validate(
            $command,
            $this->getConstraint()
        );
    }

    function it_adds_a_violation_if_the_certificate_is_wrongly_formatted(
        $context,
        ConstraintViolationBuilderInterface $builder
    )
    {
        $context->buildViolation('This is not a valid certificate.')
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        $this->validate(
            'jambon',
            new IsCertificateValid()
        );
    }

    function it_adds_a_violation_if_the_certificate_has_expired(
        $context,
        ConstraintViolationBuilderInterface $builder
    )
    {
        $context->buildViolation('This certificate has expired.')
            ->shouldBeCalled()
            ->willReturn($builder);
        $builder->addViolation()->shouldBeCalled();

        // This certificate has expired on December 20th, 2018
        $this->validate(
            'MIIDiDCCAnCgAwIBAgIJAJLjuY1vM9ULMA0GCSqGSIb3DQEBCwUAMFkxCzAJBgNVBAYTAkZSMRMwEQYDVQQIDApTb21lLVN0YXRlMQ8wDQYDVQQHDAZOYW50ZXMxDzANBgNVBAoMBkFrZW5lbzETMBEGA1UEAwwKYWtlbmVvLmNvbTAeFw0xODEyMTkxMDExMjlaFw0xODEyMjAxMDExMjlaMFkxCzAJBgNVBAYTAkZSMRMwEQYDVQQIDApTb21lLVN0YXRlMQ8wDQYDVQQHDAZOYW50ZXMxDzANBgNVBAoMBkFrZW5lbzETMBEGA1UEAwwKYWtlbmVvLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALEuXWpWNKSQWpTFFrfkWYXcAiMNrmA48/MakJXzJmKnN3NI88Iego+vr+yVZpSWibWy3Oszup7YQOJGJ1o8ONNg9S7qGBsE0aR68wA6eBt0TMZyNg0mse2oMCR/CTzPYrUj/DP/nbCWx3k97uBWhVN1gU8RzWZMhsfzO9bYDs78bWpAHSqFcOP1jBoApZKT49JXx3MwTbES3e8IvNjFlKxFwdcLI2YOiXf1FZyZyS9UQGQxaOqwvoWVTpjTkk3z9MtsGtXD/x2wWPQD+Kzf4FXwXr7D6rej56ttXhmB2LtXz8cUvduI0orXaN7R/nl91UXBYVrgUAy4VzfU7/pj2IcCAwEAAaNTMFEwHQYDVR0OBBYEFAKTwjmbuLlJJ3K/A+KtER4WzXGnMB8GA1UdIwQYMBaAFAKTwjmbuLlJJ3K/A+KtER4WzXGnMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBADOJxl+fpcepceQwO/ISr0C9Whzm0DU3OFbRbYMeoStX3NkjYizaYJn1f161UCXZSAKfIH3eLe8/ZwxO1G54eiIowjueNZYxRQ31mQyTTsQl64zNMDfY1U353Nz0yDP9QKy5PDXdBOy/t9Oy+3PO2VqiyZ/xWFRe7BtSdBiw5X0AG5blUEnLM3yaYn6hFUdt/K8TlVXKctql3ANpwLNxJ0emhDPU5OEcYPUvGeJclM3RuPOf5SP7cmbrtZ+8TaIUG/9LXQQraO+hX9B/E+l8Jcj3Wy10oToWsZfjoF9Q2Yj75e2NtLMaBuPVufGRHPdRHbsbjpIysrlxfVwwiG7MaqM=',
            new IsCertificateValid()
        );
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
