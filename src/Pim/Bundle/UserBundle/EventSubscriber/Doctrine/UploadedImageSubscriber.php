<?php

namespace Pim\Bundle\UserBundle\EventSubscriber\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\UserBundle\Entity\EntityUploadedImageInterface;

/**
 * Class UploadedImageSubscriber
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UploadedImageSubscriber implements EventSubscriber
{
    /** @var string */
    protected $webRoot;

    /**
     * Define web root path.
     *
     * @param  string                    $kernelRootDir
     * @throws \InvalidArgumentException
     */
    public function __construct($kernelRootDir)
    {
        $this->webRoot = realpath($kernelRootDir . '/../web');

        if (!$this->webRoot) {
            throw new \InvalidArgumentException('Invalid kernel root');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preUpdate',
            'prePersist',
            'postPersist',
            'postUpdate',
            'postRemove'
        ];
    }

    /**
     * Remove uploaded image if any.
     *
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof EntityUploadedImageInterface) {
            return;
        }

        $this->removeImage($entity);
    }

    /**
     * Handle preUpdate.
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof EntityUploadedImageInterface) {
            return;
        }

        if (null === $entity->getImageFile()) {
            return;
        }

        $this->removeImage($entity);
        $this->updateImageName($entity);

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $uow->recomputeSingleEntityChangeSet($em->getClassMetadata(get_class($entity)), $entity);
    }

    /**
     * Handle prePersist.
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof EntityUploadedImageInterface) {
            return;
        }

        $this->updateImageName($entity);
    }

    /**
     * Handle postPersist.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof EntityUploadedImageInterface) {
            return;
        }

        $this->handleImageUpload($entity);
    }

    /**
     * Handle postUpdate.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof EntityUploadedImageInterface) {
            return;
        }

        $this->handleImageUpload($entity);
    }

    /**
     * Move uploaded image to upload dir.
     *
     * @param LifecycleEventArgs $args
     */
    protected function handleImageUpload(EntityUploadedImageInterface $entity)
    {
        if (null === $entity->getImageFile()) {
            return;
        }

        $dir = $this->getUploadRootDir($entity);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $entity->getImageFile()->move($dir, $entity->getImage());
        $entity->unsetImageFile();
    }

    /**
     * Update uploaded image name.
     *
     * @param LifecycleEventArgs $args
     */
    protected function updateImageName(EntityUploadedImageInterface $entity)
    {
        if (null === $entity->getImageFile()) {
            return;
        }

        $filename = sha1(uniqid(mt_rand(), true));
        $entity->setImage($filename . '.' . $entity->getImageFile()->guessExtension());
    }

    /**
     * Remove image.
     *
     * @param EntityUploadedImageInterface $entity
     */
    protected function removeImage(EntityUploadedImageInterface $entity)
    {
        if (null === $entity->getImageFile()) {
            return;
        }

        $file = $this->getUploadRootDir($entity) . DIRECTORY_SEPARATOR . $entity->getImage();

        if (is_file($file) && is_writable($file)) {
            unlink($file);
        }
    }

    /**
     * Get upload directory location in FS.
     *
     * @param  EntityUploadedImageInterface $entity
     *
     * @return string
     */
    protected function getUploadRootDir(EntityUploadedImageInterface $entity)
    {
        return rtrim($this->webRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $entity->getUploadDir();
    }
}
