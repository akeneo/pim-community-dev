<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Normalizer\Versioning;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypter;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Security\CredentialsEncrypterRegistry;
use Akeneo\Platform\Bundle\ImportExportBundle\Normalizer\Versioning\JobInstanceNormalizer;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JobInstanceNormalizerSpec extends ObjectBehavior
{
    const CLEAR_SFTP_STORAGE_CONFIGURATION = [
        'type' => 'sftp',
        'username' => 'username',
        'host' => 'host',
        'port' => '22',
        'password' => 's3cr3t',
    ];

    const OBFUSCATED_SFTP_STORAGE_CONFIGURATION = [
        'type' => 'sftp',
        'username' => 'username',
        'host' => 'host',
        'port' => '22',
    ];

    function let(CredentialsEncrypter $credentialsEncrypter)
    {
        $credentialsEncrypterRegistry = new CredentialsEncrypterRegistry([$credentialsEncrypter->getWrappedObject()]);
        $credentialsEncrypter->support(self::CLEAR_SFTP_STORAGE_CONFIGURATION)->willReturn(true);
        $credentialsEncrypter->obfuscateCredentials(self::CLEAR_SFTP_STORAGE_CONFIGURATION)->willReturn(self::OBFUSCATED_SFTP_STORAGE_CONFIGURATION);
        $this->beConstructedWith($credentialsEncrypterRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobInstanceNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_job_instance_normalization_into_flat(JobInstance $jobinstance)
    {
        $this->supportsNormalization($jobinstance, 'flat')->shouldBe(true);
        $this->supportsNormalization($jobinstance, 'csv')->shouldBe(false);
        $this->supportsNormalization($jobinstance, 'json')->shouldBe(false);
        $this->supportsNormalization($jobinstance, 'xml')->shouldBe(false);
    }

    function it_normalizes_job_instance(JobInstance $jobinstance)
    {
        $jobinstance->getCode()->willReturn('product_export');
        $jobinstance->getLabel()->willReturn('Product export');
        $jobinstance->getConnector()->willReturn('myconnector');
        $jobinstance->getType()->willReturn('EXPORT');
        $jobinstance->getJobName()->willReturn('csv_product_export');
        $jobinstance->getRawParameters()->willReturn(
            [
                'delimiter' => ';'
            ]
        );

        $this->normalize($jobinstance)->shouldReturn(
            [
                'code'          => 'product_export',
                'job_name'      => 'csv_product_export',
                'label'         => 'Product export',
                'connector'     => 'myconnector',
                'type'          => 'EXPORT',
                'configuration' => '{"delimiter":";"}'
            ]
        );
    }

    function it_obfuscates_credentials_on_job_instance(JobInstance $jobinstance)
    {
        $jobinstance->getCode()->willReturn('product_export');
        $jobinstance->getLabel()->willReturn('Product export');
        $jobinstance->getConnector()->willReturn('myconnector');
        $jobinstance->getType()->willReturn('EXPORT');
        $jobinstance->getJobName()->willReturn('csv_product_export');
        $jobinstance->getRawParameters()->willReturn(
            [
                'storage' => self::CLEAR_SFTP_STORAGE_CONFIGURATION,
                'delimiter' => ';'
            ]
        );

        $this->normalize($jobinstance)->shouldReturn(
            [
                'code'          => 'product_export',
                'job_name'      => 'csv_product_export',
                'label'         => 'Product export',
                'connector'     => 'myconnector',
                'type'          => 'EXPORT',
                'configuration' => json_encode([
                    'storage' => self::OBFUSCATED_SFTP_STORAGE_CONFIGURATION,
                    'delimiter' => ';'
                ])
            ]
        );
    }
}
