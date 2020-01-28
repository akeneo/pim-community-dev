<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlDeleteVersionsByIdsQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Event\PreAdvisementVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\PrePurgeVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\PurgeVersionEvents;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
    /** @deprecated not used internally anymore */
    const BULK_THRESHOLD = 1000;

    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /** @var BulkRemoverInterface */
    protected $versionRemover;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var VersionPurgerAdvisorInterface[] */
    protected $versionPurgerAdvisors = [];

    /** @var SqlDeleteVersionsByIdsQuery */
    private $deleteVersionsByIdsQuery;

    public function __construct(
        VersionRepositoryInterface $versionRepository,
        BulkRemoverInterface $versionRemover,
        ObjectDetacherInterface $objectDetacher,
        EventDispatcherInterface $eventDispatcher,
        SqlDeleteVersionsByIdsQuery $deleteVersionsByIdsQuery
    ) {
        $this->versionRepository = $versionRepository;
        $this->versionRemover = $versionRemover;
        $this->objectDetacher = $objectDetacher;
        $this->eventDispatcher = $eventDispatcher;
        $this->deleteVersionsByIdsQuery = $deleteVersionsByIdsQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function purge(array $options = [])
    {
        $versionsToPurge = [];
        $versionsPurgedCount = 0;

        $optionResolver = new OptionsResolver();
        $this->configureOptions($optionResolver);
        $options = $optionResolver->resolve($options);

        $versionsCursor = $this->versionRepository->findPotentiallyPurgeableBy($options);

        while ($row = $versionsCursor->fetch()) {
            $version = PurgeableVersion::create($row['id'], $row['version'], $row['resource_id'], $row['resource_name']);
            $this->eventDispatcher->dispatch(
                PurgeVersionEvents::PRE_ADVISEMENT,
                new PreAdvisementVersionEvent($version)
            );

            if ($this->isVersionPurgeable($version, $options)) {
                $this->eventDispatcher->dispatch(
                    PurgeVersionEvents::PRE_PURGE,
                    new PrePurgeVersionEvent($version)
                );
                $versionsPurgedCount++;
                $versionsToPurge[] = $version->getId();

                if (count($versionsToPurge) >= $options['batch_size']) {
                    $this->deleteVersionsByIdsQuery->execute($versionsToPurge);
                    $versionsToPurge = [];
                }
            }
        }

        if (count($versionsToPurge) > 0) {
            $this->deleteVersionsByIdsQuery->execute($versionsToPurge);
        }

        return $versionsPurgedCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionsToPurgeCount(array $options)
    {
        $optionResolver = new OptionsResolver();
        $this->configureOptions($optionResolver);
        $options = $optionResolver->resolve($options);

        $versionsCursor = $this->versionRepository->findPotentiallyPurgeableBy($options);

        return $versionsCursor->rowCount();
    }

    /**
     * {@inheritdoc}
     */
    public function addVersionPurgerAdvisor(VersionPurgerAdvisorInterface $versionPurgerAdvisor)
    {
        $this->versionPurgerAdvisors[] = $versionPurgerAdvisor;
    }

    /**
     * Checks if all advisors agree on purging the version
     *
     * @param VersionInterface $version
     * @param array            $options
     *
     * @return bool
     */
    protected function isVersionPurgeable(PurgeableVersion $version, array $options = [])
    {
        foreach ($this->versionPurgerAdvisors as $advisor) {
            if ($advisor->supports($version) && !$advisor->isPurgeable($version, $options)) {
                return false;
            }
        }

        return true;
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

        $optionResolver->setNormalizer('limit_date', function(Options $options, $value) {
            return new \DateTime(
                sprintf('%d days ago', $options['days_number']),
                new \DateTimeZone('UTC')
            );
        });
    }
}
