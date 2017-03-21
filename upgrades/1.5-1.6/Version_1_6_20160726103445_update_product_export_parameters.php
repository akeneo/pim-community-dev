<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Update existing product export raw_parameters to the new format
 */
class Version_1_6_20160726103445_update_product_export_parameters extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $jobInstanceRepo = $this->container->get('pim_import_export.repository.job_instance');
        $channelRepo = $this->container->get('pim_catalog.repository.channel');
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $validator = $this->container->get('validator');

        $jobInstances = $jobInstanceRepo->findBy(['type' => 'export']);

        foreach ($jobInstances as $jobInstance) {
            $parameters = $jobInstance->getRawParameters();

            // Only product exports have a parameter named 'channel'
            if (isset($parameters['channel'])) {
                $channel = $channelRepo->findOneByIdentifier($parameters['channel']);

                if (null === $channel) {
                    continue;
                }

                $locales = $channel->getLocales();
                $localeCodes = [];
                foreach ($locales as $locale) {
                    $localeCodes[] = $locale->getCode();
                }

                $parameters['filters'] = [
                    'data' => [
                        [
                            'field'    => 'enabled',
                            'operator' => '=',
                            'value'    => true
                        ],
                        [
                            'field'    => 'categories.code',
                            'operator' => 'IN CHILDREN',
                            'value'    => [$channel->getCategory()->getCode()]
                        ],
                        [
                            'field'    => 'completeness',
                            'operator' => '>=',
                            'value'    => 100
                        ]
                    ],
                    'structure' => [
                        'scope'   => $channel->getCode(),
                        'locales' => $localeCodes
                    ]
                ];

                unset($parameters['channel']);
                $jobInstance->setRawParameters($parameters);
                $errors = $validator->validate($jobInstance);

                if (count($errors) === 0) {
                    $entityManager->persist($jobInstance);
                    $entityManager->flush();
                }
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
