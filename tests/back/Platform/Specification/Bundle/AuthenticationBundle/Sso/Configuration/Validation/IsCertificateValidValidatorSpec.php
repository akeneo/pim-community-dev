<?php

namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\Validation;

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

        // This certificate expires in 2028
        $this->validate(
            'MIIDYDCCAkigAwIBAgIJALap6dVB8+8VMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwHhcNMTgwOTIxMTIwMjA1WhcNMjgwOTIwMTIwMjA1WjBFMQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwpLmvVwtzHwHZvENivz0lYsQ3m6gnqrHXdkszonaF253ariYO7TYQlcCv/wDtewB4AQtc1BEklHsFyyaPGUtsDu14lU02DP1Zt2GH4QgCcwP9/4iODoDbRMTatguBMYb0oRUL/Q73fJ168BX81fdpMC46GcZ6gTGDmNGlEAy5vBh0uawsL5vActWfboSIvpJ2sE3NUFXUriwSotrmisnrx9VS5MyYvdjbLQWlFlwpSRcocxu7N99zvDhgh4uzH0z6YR+2zbY8Zt3h+3DN6ocfkFpytvHD1/aF4CvdwZE77BRZKcc6GTHTYr0MCtips0HCIbHJfmO8DQngJaAWu1fLwIDAQABo1MwUTAdBgNVHQ4EFgQUA/D2T/3PnBMcY/TCSvVc7dnPNqswHwYDVR0jBBgwFoAUA/D2T/3PnBMcY/TCSvVc7dnPNqswDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEAIithf+spSzTC0AlQuPuCMjCiLzn3HpRP1JvSsE0uL/SB69o1PveArywSGIJYGrORMYkL5LebTIs2mU6Tqe00+NmhvX6wdiotEShdDdgjZC1EKygcnFIF3q1CjfH0WrYMLAvhR2+qEJgLdiedLfmdGknUrM+mA7/AaZ+ZnlTOzhQau9t4ULmrCixQjvDpO/hqb0okaIjQ4XGew9AW/x8v7g0piba3RcBE0vdykDFcoLIzfx1ZS8twH2i+749DNUH3/6HTlEY2ggu6tUE0GCMxozRQ9SbNMd0Bylmo9mva4AfpED+dU4kDG2idxkho/j4kq7fAFLzn7XzKiCphMqeSzQ==',
            new IsCertificateValid()
        );
    }

    function it_adds_a_violation_if_the_certificate_is_wrongly_formatted(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('This is not a valid certificate.')
            ->shouldBeCalled()
            ->willReturn($builder)
        ;
        $builder->addViolation()->shouldBeCalled();

        $this->validate(
            'jambon',
            new IsCertificateValid()
        );
    }

    function it_adds_a_violation_if_the_certificate_has_expired(
        $context,
        ConstraintViolationBuilderInterface $builder
    ) {
        $context->buildViolation('This certificate has expired.')
            ->shouldBeCalled()
            ->willReturn($builder)
        ;
        $builder->addViolation()->shouldBeCalled();

        // This certificate has expired on December 20th, 2018
        $this->validate(
            'MIIDiDCCAnCgAwIBAgIJAJLjuY1vM9ULMA0GCSqGSIb3DQEBCwUAMFkxCzAJBgNVBAYTAkZSMRMwEQYDVQQIDApTb21lLVN0YXRlMQ8wDQYDVQQHDAZOYW50ZXMxDzANBgNVBAoMBkFrZW5lbzETMBEGA1UEAwwKYWtlbmVvLmNvbTAeFw0xODEyMTkxMDExMjlaFw0xODEyMjAxMDExMjlaMFkxCzAJBgNVBAYTAkZSMRMwEQYDVQQIDApTb21lLVN0YXRlMQ8wDQYDVQQHDAZOYW50ZXMxDzANBgNVBAoMBkFrZW5lbzETMBEGA1UEAwwKYWtlbmVvLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALEuXWpWNKSQWpTFFrfkWYXcAiMNrmA48/MakJXzJmKnN3NI88Iego+vr+yVZpSWibWy3Oszup7YQOJGJ1o8ONNg9S7qGBsE0aR68wA6eBt0TMZyNg0mse2oMCR/CTzPYrUj/DP/nbCWx3k97uBWhVN1gU8RzWZMhsfzO9bYDs78bWpAHSqFcOP1jBoApZKT49JXx3MwTbES3e8IvNjFlKxFwdcLI2YOiXf1FZyZyS9UQGQxaOqwvoWVTpjTkk3z9MtsGtXD/x2wWPQD+Kzf4FXwXr7D6rej56ttXhmB2LtXz8cUvduI0orXaN7R/nl91UXBYVrgUAy4VzfU7/pj2IcCAwEAAaNTMFEwHQYDVR0OBBYEFAKTwjmbuLlJJ3K/A+KtER4WzXGnMB8GA1UdIwQYMBaAFAKTwjmbuLlJJ3K/A+KtER4WzXGnMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBADOJxl+fpcepceQwO/ISr0C9Whzm0DU3OFbRbYMeoStX3NkjYizaYJn1f161UCXZSAKfIH3eLe8/ZwxO1G54eiIowjueNZYxRQ31mQyTTsQl64zNMDfY1U353Nz0yDP9QKy5PDXdBOy/t9Oy+3PO2VqiyZ/xWFRe7BtSdBiw5X0AG5blUEnLM3yaYn6hFUdt/K8TlVXKctql3ANpwLNxJ0emhDPU5OEcYPUvGeJclM3RuPOf5SP7cmbrtZ+8TaIUG/9LXQQraO+hX9B/E+l8Jcj3Wy10oToWsZfjoF9Q2Yj75e2NtLMaBuPVufGRHPdRHbsbjpIysrlxfVwwiG7MaqM=',
            new IsCertificateValid()
        );
    }
}
