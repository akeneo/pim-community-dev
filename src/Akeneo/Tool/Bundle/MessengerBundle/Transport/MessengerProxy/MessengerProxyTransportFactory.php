<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Messenger\LaunchProductAndProductModelEvaluationsMessage;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MessengerProxyTransportFactory implements TransportFactoryInterface
{
    public function __construct(
        private TransportFactoryInterface $gcpTransportFactory,
        private readonly string $googleCloudProject,
        private readonly SerializerInterface $gpsSerializer,
        private readonly string $env
    ) {
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        // TODO: create transport depending on the env and the topics/consumers in config file (see MessengerConfigBuilder)
        if ($this->env === 'dev') {
            return new MessengerProxyTransport(
                [
                    LaunchProductAndProductModelEvaluationsMessage::class => $this->gcpTransportFactory->createTransport('gps:',
                        [
                            'project_id' => $this->googleCloudProject,
                            'topic_name' => 'launch_product_and_product_model_evaluations_queue', // TODO: use the config file
                            'auto_setup' => true, //\in_array($this->env, ['dev', 'test', 'test_fake']),
                        ], $this->gpsSerializer),
                ],
                $this->gcpTransportFactory->createTransport('gps:', [
                    'project_id' => $this->googleCloudProject,
                    'topic_name' => 'launch_product_and_product_model_evaluations_queue', // TODO: use the config file
                    'auto_setup' => true, //\in_array($this->env, ['dev', 'test', 'test_fake']),
                ], $this->gpsSerializer),
                'dev'
            );
        }
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'akeneo-messenger-proxy:');
    }
}
