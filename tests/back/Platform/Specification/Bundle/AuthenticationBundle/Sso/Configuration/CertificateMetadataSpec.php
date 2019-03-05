<?php


namespace Specification\Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Configuration\CertificateMetadata;
use Akeneo\Platform\Component\Authentication\Sso\Configuration\CertificateExpirationDate;
use PhpSpec\ObjectBehavior;

class CertificateMetadataSpec extends ObjectBehavior
{
    private const CERTIFICATE_EXPIRES_AT = 'Wed, 13 Sep 2028 09:30:13 +0000';

    function let()
    {
        $this->beConstructedWith('MIIDYDCCAkigAwIBAgIJAOGDWOB07tCyMA0GCSqGSIb3DQEBCwUAMEUxCzAJBgNVBAYTAkFVMRMwEQYDVQQIDApTb21lLVN0YXRlMSEwHwYDVQQKDBhJbnRlcm5ldCBXaWRnaXRzIFB0eSBMdGQwHhcNMTgwOTE0MDkzMDEzWhcNMjgwOTEzMDkzMDEzWjBFMQswCQYDVQQGEwJBVTETMBEGA1UECAwKU29tZS1TdGF0ZTEhMB8GA1UECgwYSW50ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4J5iDNmrQLn4NHVvjTR0Z+xqmW6mYWFP/MxI4D4urwv6J0CLZppxfcSXLYogxrC5U+JxlF7jv9CM6Dpvkc4xBFyCNVIKAwBh/W+fL85m48Fd7Nh1VW+fK8ZBDUKFfuRxK+H/0shU96z2onVB6uYiNxF0+26MwZwjecLIh6st+pEKzd2aUNgB9RYPJWqdxw8R5mZH2EfzjTDKyomAeENcVW6zK9kQP6YNC7T8mYaUus4jhAcC/jV8Iqy7Oc1h+tEQV3rqFLLKezuNZWufoOrzaPoKMOkXgasxtadM1wU9InIpiO6pWPCwNc6TLpmZCcry6yIoveMx5fzMGjgxmmmUrwIDAQABo1MwUTAdBgNVHQ4EFgQUitGGamyDFTInis6Umd+Wc+NoFoMwHwYDVR0jBBgwFoAUitGGamyDFTInis6Umd+Wc+NoFoMwDwYDVR0TAQH/BAUwAwEB/zANBgkqhkiG9w0BAQsFAAOCAQEA05pnfsR6Atm9Wx+fdaFG7DVnrQjLDXRCXqkRJ09ygrpJlIF6YBPXcYA0kidoVlBbkZhWzz1ht5i1BYKzKdWzB2BQZLUnkaM8UotKFjdHu7/7vnM7w/n3S5zx3gtoCMSegp9vk6H2wjsPYfR0mVJOYcFzRY48bdQLV6nJRU3gV+ZikM/u92xArcaTCS6l4YEBCqJWtvlVojc6nwwv262t6NJ8NHRHqV98aoNMO4ltjFIkXa0xtNqYo7pI01kkTlPrignb4djZjCpdwu/lZJTy4FAra4lTdu2j4nn8QxNKDoBIrsNx6b+C767Mtf1f3JSRMAvt/IE4Wjp5IIAeLsTHSA==');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CertificateMetadata::class);
    }

    function it_gives_the_certificate_expiration_date()
    {
        $expectedDate = new CertificateExpirationDate(self::CERTIFICATE_EXPIRES_AT);
        $this->getExpirationDate()->shouldBeLike($expectedDate);
    }
}
