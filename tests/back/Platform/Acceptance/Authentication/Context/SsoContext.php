<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Acceptance\Authentication\Context;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\CreateOrUpdateConfiguration;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class SsoContext implements Context
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var CreateOrUpdateConfiguration */
    private $command;

    /** @var array */
    private $errors = [];

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @Given a configuration
     */
    public function aConfiguration(): void
    {
        $this->command = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            'https://idp.jambon.com',
            'https://idp.jambon.com/login',
            'https://idp.jambon.com/logout',
            'MIIDYDCCAkigAwIBAgIJAOGDWOB07tCyMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwHhcNMTgwOTE0MDkzMDEzWhcNMjgwOTEzMDkzMDEzWjBFMQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4J5iDNmrQLn4NHVvjTR0Z+xqmW6mYWFP/MxI4D4urwv6J0CLZppxfcSXLYogxrC5U+JxlF7jv9CM6Dpvkc4xBFyCNVIKAwBh/W+fL85m48Fd7Nh1VW+fK8ZBDUKFfuRxK+H/0shU96z2onVB6uYiNxF0+26MwZwjecLIh6st+pEKzd2aUNgB9RYPJWqdxw8R5mZH2EfzjTDKyomAeENcVW6zK9kQP6YNC7T8mYaUus4jhAcC/jV8Iqy7Oc1h+tEQV3rqFLLKezuNZWufoOrzaPoKMOkXgasxtadM1wU9InIpiO6pWPCwNc6TLpmZCcry6yIoveMx5fzMGjgxmmmUrwIDAQABo1MwUTAdBgNVHQ4EFgQUitGGamyDFTInis6Umd+Wc+NoFoMwHwYDVR0jBBgwFoAUitGGamyDFTInis6Umd+Wc+NoFoMwDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEA05pnfsR6Atm9Wx+fdaFG7DVnrQjLDXRCXqkRJ09ygrpJlIF6YBPXcYA0kidoVlBbkZhWzz1ht5i1BYKzKdWzB2BQZLUnkaM8UotKFjdHu7/7vnM7w/n3S5zx3gtoCMSegp9vk6H2wjsPYfR0mVJOYcFzRY48bdQLV6nJRU3gV+ZikM/u92xArcaTCS6l4YEBCqJWtvlVojc6nwwv262t6NJ8NHRHqV98aoNMO4ltjFIkXa0xtNqYo7pI01kkTlPrignb4djZjCpdwu/lZJTy4FAra4lTdu2j4nn8QxNKDoBIrsNx6b+C767Mtf1f3JSRMAvt/IE4Wjp5IIAeLsTHSA==',
            'https://my-pim.com/saml/metadata',
            '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'
        );

        $this->buildErrors($this->command);
    }

    /**
     * @Given an empty configuration
     */
    public function anEmptyConfiguration(): void
    {
        $this->command = new CreateOrUpdateConfiguration('', false, '', '', '', '', '', '', '');
        $this->buildErrors($this->command);
    }

    /**
     * @Given a configuration with invalid URLs
     */
    public function aConfigurationWithInvalidURLs(): void
    {
        $this->command = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            'not an URL',
            'not an URL',
            'not an URL',
            'MIIDYDCCAkigAwIBAgIJAOGDWOB07tCyMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwHhcNMTgwOTE0MDkzMDEzWhcNMjgwOTEzMDkzMDEzWjBFMQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4J5iDNmrQLn4NHVvjTR0Z+xqmW6mYWFP/MxI4D4urwv6J0CLZppxfcSXLYogxrC5U+JxlF7jv9CM6Dpvkc4xBFyCNVIKAwBh/W+fL85m48Fd7Nh1VW+fK8ZBDUKFfuRxK+H/0shU96z2onVB6uYiNxF0+26MwZwjecLIh6st+pEKzd2aUNgB9RYPJWqdxw8R5mZH2EfzjTDKyomAeENcVW6zK9kQP6YNC7T8mYaUus4jhAcC/jV8Iqy7Oc1h+tEQV3rqFLLKezuNZWufoOrzaPoKMOkXgasxtadM1wU9InIpiO6pWPCwNc6TLpmZCcry6yIoveMx5fzMGjgxmmmUrwIDAQABo1MwUTAdBgNVHQ4EFgQUitGGamyDFTInis6Umd+Wc+NoFoMwHwYDVR0jBBgwFoAUitGGamyDFTInis6Umd+Wc+NoFoMwDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEA05pnfsR6Atm9Wx+fdaFG7DVnrQjLDXRCXqkRJ09ygrpJlIF6YBPXcYA0kidoVlBbkZhWzz1ht5i1BYKzKdWzB2BQZLUnkaM8UotKFjdHu7/7vnM7w/n3S5zx3gtoCMSegp9vk6H2wjsPYfR0mVJOYcFzRY48bdQLV6nJRU3gV+ZikM/u92xArcaTCS6l4YEBCqJWtvlVojc6nwwv262t6NJ8NHRHqV98aoNMO4ltjFIkXa0xtNqYo7pI01kkTlPrignb4djZjCpdwu/lZJTy4FAra4lTdu2j4nn8QxNKDoBIrsNx6b+C767Mtf1f3JSRMAvt/IE4Wjp5IIAeLsTHSA==',
            'not an URL',
            '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'
        );
        $this->buildErrors($this->command);
    }

    /**
     * @Given a configuration with invalid certificates
     */
    public function aConfigurationWithInvalidCertificates(): void
    {
        $this->command = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            'https://idp.jambon.com',
            'https://idp.jambon.com/login',
            'https://idp.jambon.com/logout',
            'this is not a valid certificate',
            'https://my-pim.com/saml/metadata',
            'neither this',
            'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDCkua9XC3MfAdm8Q2K/PSVixDebqCeqsdd2SzOidoXbndquJg7tNhCVwK//AO17AHgBC1zUESSUewXLJo8ZS2wO7XiVTTYM/Vm3YYfhCAJzA/3/iI4OgNtExNq2C4ExhvShFQv9Dvd8nXrwFfzV92kwLjoZxnqBMYOY0aUQDLm8GHS5rCwvm8By1Z9uhIi+knawTc1QVdSuLBKi2uaKyevH1VLkzJi92NstBaUWXClJFyhzG7s333O8OGCHi7MfTPphH7bNtjxm3eH7cM3qhx+QWnK28cPX9oXgK93BkTvsFFkpxzoZMdNivQwK2KmzQcIhscl+Y7wNCeAloBa7V8vAgMBAAECggEARfBR2jz5BWiLrJf2+z+jluFO5fUz7PSMBxLaRT9N0mBVslm59rQxi98E0QeAOfMkVWaLA8kVbNbfmxDgO9dOjetYnItuHEzI5/m2jTuL3JmqH8UMfdN0ic9yilQ8bmDbfVctf7M1lbjiZCRWONUbiW3wyTDOIs0md+N9aiqNYW7jhFtWm/1wcLaPVMlCELBHFr/6fo/D5uoo6l5rqTPEq+fdwNC9QlEd0NN/05H3XmMd2SLY9kW131tJw00sP+YhOIqeBluNjdjIojM2bOcV6N/8qu3vB+P3tEwLSPAPT8WCKPcnkW2tt1gpNnJ//+YkxDKuRGdMprW3gYmP/TtWcQKBgQDwmKc9IV5OYgszvMSanBuhCPpMlR69ht5EtGdnrBZy5K+OUwbWm/pH17W4RRrSDibxGQ6LEekBuNSKmIOPZH6TbBH17JVlbQMjzquOddzlNUMbxULiLgf5nGpqkDV7MuMlo+v2UM2I7EmM8H9fZJriA62pf0UyAbMbmAiGXd61dwKBgQDPB/HkZCfoDCP70AZ8lKgWJM1Y5o1bDV8JBoxGh1ZQL2uTR1+/Ys9rm+8zlCf5CL48Oen6Os4Dcr3QljhgbgAHCXwR9/LQu6JPD9Foc4p7byN5y8xy4nZt3Gfw7jZiIYKiFM6/3lV+xKYVGLR+xYDe44j8iak7/i55iNiNg4ZyCQKBgAZ0MdhD8uGrY52JrMRw95TERuKTBXYUDhZNuJBhX2DJnaP1ujM7j+UpdihxQhzsYEMLZwZ3/oYbTShCmxTXn0WZGoo8RG2qFPF688MoijpjyV8PVZH3piMd/QTKxYR+gvVZhlTfKgRIQljTgrcuXbE+ZAQt8885mHJfC6t/DSBVAoGAGrC6hahH4MmX5gHmpC8CEIDEHH70oFVz+BTpBrqx7PqAEuezt1fEx+m9h9fE4302EUuiF4l3P8iOIhMLx5rG1CFr5mEh47kc7rZqV931b35fg3c7r1/0xqsQl2YOis4pKj4mfWPrf8FYbAXYVzOdLp8E7RHOSJa3ZSG4UV4wegkCgYEAjx/fOLGe//NNPdODq2kAjbAJbs0+Kypqd1EdN/CRXIjfIpDIqPVsLISTjF9pbBSc+/J9KqUjMJNKBuTibvA35f2uvVnhPoLQmMGu7n/QRgcY0msGvB9ezrgLrbnRDcQnKNxSyNsDS9Kd+yrl+p08LB2HWsVmCNhGlPXzFoMH/PI='
        );
        $this->buildErrors($this->command);
    }

    /**
     * @Given a configuration with an expired IdP public certificate
     */
    public function aConfigurationWithAnExpiredIdPPublicCertificate(): void
    {
        $this->command = new CreateOrUpdateConfiguration(
            'authentication_sso',
            true,
            'https://idp.jambon.com',
            'https://idp.jambon.com/login',
            'https://idp.jambon.com/logout',
            // This certificate has expired on December 20th, 2018
            'MIIDiDCCAnCgAwIBAgIJAJLjuY1vM9ULMA0GCSqGSIb3DQEBCwUAMFkxCzAJBgNVBAYTAkZSMRMwEQYDVQQIDApTb21lLVN0YXRlMQ8wDQYDVQQHDAZOYW50ZXMxDzANBgNVBAoMBkFrZW5lbzETMBEGA1UEAwwKYWtlbmVvLmNvbTAeFw0xODEyMTkxMDExMjlaFw0xODEyMjAxMDExMjlaMFkxCzAJBgNVBAYTAkZSMRMwEQYDVQQIDApTb21lLVN0YXRlMQ8wDQYDVQQHDAZOYW50ZXMxDzANBgNVBAoMBkFrZW5lbzETMBEGA1UEAwwKYWtlbmVvLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBALEuXWpWNKSQWpTFFrfkWYXcAiMNrmA48/MakJXzJmKnN3NI88Iego+vr+yVZpSWibWy3Oszup7YQOJGJ1o8ONNg9S7qGBsE0aR68wA6eBt0TMZyNg0mse2oMCR/CTzPYrUj/DP/nbCWx3k97uBWhVN1gU8RzWZMhsfzO9bYDs78bWpAHSqFcOP1jBoApZKT49JXx3MwTbES3e8IvNjFlKxFwdcLI2YOiXf1FZyZyS9UQGQxaOqwvoWVTpjTkk3z9MtsGtXD/x2wWPQD+Kzf4FXwXr7D6rej56ttXhmB2LtXz8cUvduI0orXaN7R/nl91UXBYVrgUAy4VzfU7/pj2IcCAwEAAaNTMFEwHQYDVR0OBBYEFAKTwjmbuLlJJ3K/A+KtER4WzXGnMB8GA1UdIwQYMBaAFAKTwjmbuLlJJ3K/A+KtER4WzXGnMA8GA1UdEwEB/wQFMAMBAf8wDQYJKoZIhvcNAQELBQADggEBADOJxl+fpcepceQwO/ISr0C9Whzm0DU3OFbRbYMeoStX3NkjYizaYJn1f161UCXZSAKfIH3eLe8/ZwxO1G54eiIowjueNZYxRQ31mQyTTsQl64zNMDfY1U353Nz0yDP9QKy5PDXdBOy/t9Oy+3PO2VqiyZ/xWFRe7BtSdBiw5X0AG5blUEnLM3yaYn6hFUdt/K8TlVXKctql3ANpwLNxJ0emhDPU5OEcYPUvGeJclM3RuPOf5SP7cmbrtZ+8TaIUG/9LXQQraO+hX9B/E+l8Jcj3Wy10oToWsZfjoF9Q2Yj75e2NtLMaBuPVufGRHPdRHbsbjpIysrlxfVwwiG7MaqM=',
            'https://my-pim.com/saml/metadata',
            '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'
        );
        $this->buildErrors($this->command);
    }

    /**
     * @Then I should have no validation errors
     */
    public function iShouldHaveNoValidationErrors(): void
    {
        Assert::assertEmpty($this->errors);
    }

    /**
     * @Then I should have the following validation errors:
     */
    public function iShouldHaveTheFollowingValidationErrors(TableNode $table): void
    {
        Assert::assertEquals($table->getHash(), $this->errors);
    }

    private function buildErrors(CreateOrUpdateConfiguration $command): void
    {
        $errors = $this->validator->validate($command);
        foreach ($errors as $error) {
            array_push($this->errors, ['path' => $error->getPropertyPath(), 'message' => $error->getMessage()]);
        }
    }
}
