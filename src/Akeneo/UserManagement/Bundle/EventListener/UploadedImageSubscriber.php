<?php

namespace Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\UserManagement\Component\EntityUploadedImageInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UploadedImageSubscriber implements EventSubscriber
{
    protected string $webRoot;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(string $kernelProjectDir)
    {
        $this->webRoot = realpath($kernelProjectDir . '/public');
        if (!$this->webRoot) {
            throw new \InvalidArgumentException('Invalid kernel root');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            'preUpdate',
            'prePersist',
            'postPersist',
            'postUpdate',
            'postRemove',
        ];
    }

    /**
     * Remove uploaded image if any.
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        /** @var EntityUploadedImageInterface $entity */
        $entity = $args->getEntity();
        $this->removeImage($entity);
    }

    /**
     * Handle preUpdate.
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        /** @var EntityUploadedImageInterface $entity */
        $entity = $args->getEntity();
        if ($this->hasUploadedImage($entity)) {
            $this->removeImage($entity);

            $this->updateImageName($args);

            $em = $args->getEntityManager();
            $uow = $em->getUnitOfWork();
            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(get_class($entity)),
                $entity
            );
        }
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->updateImageName($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handleImageUpload($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handleImageUpload($args);
    }

    /**
     * Move uploaded image to upload dir.
     */
    protected function handleImageUpload(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($this->isExpectedEntity($entity)) {
            if (!$this->hasUploadedImage($entity)) {
                return;
            }

            $dir = $this->getUploadRootDir($entity);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $entity->getImageFile()->move($dir, $entity->getImage());
            $entity->unsetImageFile();
        }
    }

    /**
     * Update uploaded image name.
     */
    protected function updateImageName(LifecycleEventArgs $args)
    {
        /** @var EntityUploadedImageInterface $entity */
        $entity = $args->getEntity();
        if ($this->hasUploadedImage($entity)) {
            $filename = sha1(uniqid(mt_rand(), true));
            $entity->setImage($filename . '.' . $entity->getImageFile()->guessExtension());
        }
    }

    protected function getUploadRootDir(EntityUploadedImageInterface $entity): string
    {
        return rtrim($this->webRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $entity->getUploadDir();
    }

    protected function removeImage(EntityUploadedImageInterface $entity): void
    {
        if ($this->isExpectedEntity($entity) && $entity->getImage()) {
            $file = $this->getUploadRootDir($entity) . DIRECTORY_SEPARATOR . $entity->getImage();
            if (is_file($file) && is_writable($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Check for new image upload.
     */
    protected function hasUploadedImage(EntityUploadedImageInterface $entity): bool
    {
        return $this->isExpectedEntity($entity) && null !== $entity->getImageFile();
    }

    /**
     * Check if entity acceptable by subscriber.
     */
    protected function isExpectedEntity(object $entity): bool
    {
        return $entity instanceof EntityUploadedImageInterface;
    }
}
