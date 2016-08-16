<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Family saver, contains custom logic for family's product saving
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                  $objectManager
     * @param CompletenessManager            $completenessManager
     * @param SavingOptionsResolverInterface $optionsResolver
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->objectManager = $objectManager;
        $this->completenessManager = $completenessManager;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($family, array $options = [])
    {
        $this->validateFamily($family);

        $options = $this->optionsResolver->resolveSaveOptions($options);

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($family));

        $this->objectManager->persist($family);

        $this->completenessManager->scheduleForFamily($family);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($family));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $families, array $options = [])
    {
        if (empty($families)) {
            return;
        }

        $allOptions = $this->optionsResolver->resolveSaveAllOptions($options);

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($families));

        foreach ($families as $family) {
            $this->validateFamily($family);

            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($family));

            $this->objectManager->persist($family);

            $this->completenessManager->scheduleForFamily($family);
        }

        $this->objectManager->flush();

        foreach ($families as $family) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($family));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($families));
    }

    /**
     * @param $family
     */
    protected function validateFamily($family)
    {
        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\FamilyInterface", "%s" provided.',
                    ClassUtils::getClass($family)
                )
            );
        }
    }
}
