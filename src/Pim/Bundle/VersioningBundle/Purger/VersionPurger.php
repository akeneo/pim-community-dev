<?php

namespace Pim\Bundle\VersioningBundle\Purger;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\Versioning\Model\VersionInterface;
use Pim\Bundle\VersioningBundle\Event\PreAdvisementVersionEvent;
use Pim\Bundle\VersioningBundle\Event\PrePurgeVersionEvent;
use Pim\Bundle\VersioningBundle\Event\PurgeVersionEvents;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
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
    const BULK_THRESHOLD = 1000;

    /** @var VersionRepositoryInterface */
    protected $versionRepository;

    /** @var BulkRemoverInterface */
    protected $versionRemover;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var array */
    protected $versionPurgerAdvisors = [];

    /**
     * @param VersionRepositoryInterface $versionRepository
     * @param BulkRemoverInterface       $versionRemover
     * @param ObjectDetacherInterface    $objectDetacher
     * @param EventDispatcherInterface   $eventDispatcher
     */
    public function __construct(
        VersionRepositoryInterface $versionRepository,
        BulkRemoverInterface $versionRemover,
        ObjectDetacherInterface $objectDetacher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->versionRepository = $versionRepository;
        $this->versionRemover = $versionRemover;
        $this->objectDetacher = $objectDetacher;
        $this->eventDispatcher = $eventDispatcher;
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

        $versionsPaginator = $this->versionRepository->findPotentiallyPurgeableBy($options);

        foreach ($versionsPaginator as $version) {
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
                $versionsToPurge[] = $version;

                if (count($versionsToPurge) >= self::BULK_THRESHOLD) {
                    $this->versionRemover->removeAll($versionsToPurge);
                    $this->objectDetacher->detachAll($versionsToPurge);
                    $versionsToPurge = [];
                }
            } else {
                $this->objectDetacher->detach($version);
            }
        }

        $this->versionRemover->removeAll($versionsToPurge);
        $this->objectDetacher->detachAll($versionsToPurge);

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

        $versionsPaginator = $this->versionRepository->findPotentiallyPurgeableBy($options);

        return $versionsPaginator->count();
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
    protected function isVersionPurgeable(VersionInterface $version, array $options = [])
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
     *
     * @return OptionsResolver
     */
    protected function configureOptions(OptionsResolver $optionResolver)
    {
        $optionResolver->setDefaults(
            [
                'resource_name' => null,
                'days_number'   => 90,
                'date_operator' => '<',
                'limit_date'    => new \DateTime('now', new \DateTimeZone('UTC')),
            ]
        );
        $optionResolver
            ->setAllowedTypes('days_number', 'int')
            ->setAllowedValues('date_operator', ['<', '>']);

        $optionResolver->setNormalizer('limit_date', function (Options $options, $value) {
            return new \DateTime(
                sprintf('%d days ago', $options['days_number']),
                new \DateTimeZone('UTC')
            );
        });
    }
}
