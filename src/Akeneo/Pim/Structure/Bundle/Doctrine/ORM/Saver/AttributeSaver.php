<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Attribute saver
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSaver implements SaverInterface, BulkSaverInterface
{
    protected EntityManagerInterface $entityManager;
    protected EventDispatcherInterface $eventDispatcher;
    /** @var SaverInterface[] */
    protected array $additionalSavers;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        array $additionalSavers = []
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->additionalSavers = $additionalSavers;
    }

    /**
     * {@inheritdoc}
     */
    public function save($attribute, array $options = [])
    {
        $this->validateAttribute($attribute);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::PRE_SAVE);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($attribute);
            $this->entityManager->flush();

            foreach ($this->additionalSavers as $additionalSaver) {
                $additionalSaver->save($attribute, $options);
            }

            $this->entityManager->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }

        $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $attributes, array $options = [])
    {
        if (empty($attributes)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new GenericEvent($attributes, $options), StorageEvents::PRE_SAVE_ALL);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            foreach ($attributes as $attribute) {
                $this->validateAttribute($attribute);
                $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::PRE_SAVE);

                $this->entityManager->persist($attribute);
            }
            $this->entityManager->flush();
            foreach ($attributes as $attribute) {
                foreach ($this->additionalSavers as $additionalSaver) {
                    $additionalSaver->save($attribute, $options);
                }
            }

            $this->entityManager->getConnection()->commit();
        } catch (\Throwable $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }

        foreach ($attributes as $attribute) {
            $this->eventDispatcher->dispatch(new GenericEvent($attribute, $options), StorageEvents::POST_SAVE);
        }

        $this->eventDispatcher->dispatch(new GenericEvent($attributes, $options), StorageEvents::POST_SAVE_ALL);
    }

    protected function validateAttribute($attribute)
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Pim\Structure\Component\Model\AttributeInterface", "%s" provided.',
                    ClassUtils::getClass($attribute)
                )
            );
        }
    }
}
