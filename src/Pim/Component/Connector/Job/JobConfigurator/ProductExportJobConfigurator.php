<?php

namespace Pim\Component\Connector\Job\JobConfigurator;

use Akeneo\Component\Batch\Job\JobConfiguratorInterface;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Localization\Localizer\LocalizerInterface;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * DefaultParameters for product CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExportJobConfigurator implements JobConfiguratorInterface
{
    /** @var array */
    protected $supportedJobNames;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param array                      $supportedJobNames
     */
    public function __construct(ChannelRepositoryInterface $channelRepository, array $supportedJobNames)
    {
        $this->channelRepository = $channelRepository;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'decimalSeparator' => LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR,
            'dateFormat' => LocalizerInterface::DEFAULT_DATE_FORMAT,
            'enabled' => 'enabled',
            'channel' => null,
            'fields' => [
                'decimalSeparator' => new NotBlank(),
                'dateFormat' => new NotBlank(),
                'channel' => [
                    new NotBlank(['groups' => 'Execution']),
                    new Channel()
                ],
                'enabled' => new NotBlank(['groups' => 'Execution']),
            ]
        ]);

        $resolver->setNormalizer('channel', function ($channel) {
            return $this->channelRepository->findOneByIdentifier($channel);
        });

        $resolver->setNormalizer('enabled', function ($status) {
            switch ($status) {
                case 'enabled':
                    return true;
                case 'disabled':
                    return false;
                default:
                    return null;
            }
        });

        $resolver->setDefault('filters', function (Options $options) {
            $filters = [
                [
                    'field'    => 'completeness',
                    'operator' => Operators::EQUALS,
                    'value'    => 100,
                    'context'  => []
                ],
                [
                    'field'    => 'categories.id',
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value'    => [$options['channel']->getCategory()->getId()],
                    'context'  => []
                ]
            ];

            if (null !== $options['enabled']) {
                $filters[] = [
                    'field'    => 'enabled',
                    'operator' => Operators::EQUALS,
                    'value'    => $options['enabled'],
                    'context'  => []
                ];
            }

            return $filters;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
