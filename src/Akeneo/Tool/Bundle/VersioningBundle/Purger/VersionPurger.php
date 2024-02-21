<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlDeleteVersionsByIdsQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetAllResourceNamesQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetPurgeableVersionListQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Purge versions according to registered advisors
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionPurger implements VersionPurgerInterface
{
    /** @var VersionPurgerAdvisorInterface[] */
    protected array $versionPurgerAdvisors = [];

    public function __construct(
        private SqlDeleteVersionsByIdsQuery $deleteVersionsByIdsQuery,
        private SqlGetAllResourceNamesQuery $getAllResourceNamesQuery,
        private SqlGetPurgeableVersionListQuery $getPurgeableVersionListQuery,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function purge(array $options)
    {
        $optionResolver = new OptionsResolver();
        $this->configureOptions($optionResolver);
        $options = $optionResolver->resolve($options);

        $resourceNamesToPurge = $this->getResourceNamesToPurge($options);
        $resourceNamesToPurgeCount = 1;

        foreach ($resourceNamesToPurge as $resourceName) {
            $this->logger->info(
                sprintf(
                    'Start purging versions of %s (%d/%d)',
                    $resourceName,
                    $resourceNamesToPurgeCount,
                    count($resourceNamesToPurge)
                ),
                ['options' => $options]
            );
            $purgedVersions = $this->purgeVersionsByResourceName($resourceName, $options);
            $this->logger->info(
                $purgedVersions > 0 ? sprintf(
                    'Successfully deleted %d versions.',
                    $purgedVersions
                ) : 'There are no versions to purge.'
            );
            $resourceNamesToPurgeCount++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addVersionPurgerAdvisor(VersionPurgerAdvisorInterface $versionPurgerAdvisor)
    {
        $this->versionPurgerAdvisors[] = $versionPurgerAdvisor;
    }

    private function purgeVersionsByResourceName($resourceName, array $options): int
    {
        $versionsToPurgeCount = $this->countVersionsToPurge($resourceName);
        $this->logger->info(sprintf('Versions count = %d', $versionsToPurgeCount));

        if (0 === $versionsToPurgeCount) {
            return 0;
        }

        $versionsToPurge = $this->getVersionsToPurge($resourceName, $options);
        $purgedVersionsCount = 0;

        foreach ($versionsToPurge as $purgeableVersionList) {
            $purgeableVersionList = $this->filterPurgeableVersionList($purgeableVersionList);

            if ($purgeableVersionList->count() > 0) {
                $this->deleteVersionsByIdsQuery->execute($purgeableVersionList->getVersionIds());
                $purgedVersionsCount += count($purgeableVersionList);
            }
        }

        return $purgedVersionsCount;
    }

    private function filterPurgeableVersionList(PurgeableVersionList $purgeableVersionList): PurgeableVersionList
    {
        foreach ($this->versionPurgerAdvisors as $advisor) {
            if ($advisor->supports($purgeableVersionList)) {
                $purgeableVersionList = $advisor->isPurgeable($purgeableVersionList);
            }
        }

        return $purgeableVersionList;
    }

    /**
     * Configure an option resolver with default option values
     *
     * @param OptionsResolver $optionResolver
     */
    protected function configureOptions(OptionsResolver $optionResolver)
    {
        $optionResolver->setDefaults(
            [
                'resource_name' => null,
                'days_number' => 90,
                'date_operator' => '<',
                'limit_date' => new \DateTime('now', new \DateTimeZone('UTC')),
                'batch_size' => 100,
            ]
        );
        $optionResolver
            ->setAllowedTypes('days_number', 'int')
            ->setAllowedTypes('batch_size', 'int')
            ->setAllowedValues('date_operator', ['<', '>']);

        $optionResolver->setNormalizer('limit_date', function (Options $options, $value) {
            return new \DateTime(
                sprintf('%d days ago', $options['days_number']),
                new \DateTimeZone('UTC')
            );
        });
    }

    private function getResourceNamesToPurge(array $options): array
    {
        if (null !== $options['resource_name']) {
            return [$options['resource_name']];
        }

        return $this->getAllResourceNamesQuery->execute();
    }

    private function countVersionsToPurge($resourceName): int
    {
        return $this->getPurgeableVersionListQuery->countByResource($resourceName);
    }

    private function getVersionsToPurge($resourceName, array $options): iterable
    {
        return '>' === $options['date_operator']
            ? $this->getPurgeableVersionListQuery->youngerThan(
                $resourceName,
                $options['limit_date'],
                $options['batch_size']
            )
            : $this->getPurgeableVersionListQuery->olderThan(
                $resourceName,
                $options['limit_date'],
                $options['batch_size']
            );
    }
}
