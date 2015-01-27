<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Factory\MediaFactory;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * A collector of changes that a client is sending to a product edit form
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class CollectProductMassEditValuesSubscriber implements EventSubscriberInterface
{
    /** @var ChangesCollector */
    protected $collector;

    /** @var MediaManager */
    protected $mediaManager;

    /** @var MediaFactory */
    protected $factory;

    /** @var LocaleRepositoryInterface */
    protected $repository;

    /**
     * @param ChangesCollector          $collector
     * @param MediaManager              $mediaManager
     * @param MediaFactory              $factory
     * @param LocaleRepositoryInterface $repository
     */
    public function __construct(
        ChangesCollector $collector,
        MediaManager $mediaManager,
        MediaFactory $factory,
        LocaleRepositoryInterface $repository
    ) {
        $this->collector = $collector;
        $this->mediaManager = $mediaManager;
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'collect',
        ];
    }

    /**
     * Collect changes that client sent to the product values
     *
     * @param FormEvent $event
     */
    public function collect(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!array_key_exists('values', $data) || !array_key_exists('locale', $data)) {
            return;
        }

        $locale = $this->repository->find($data['locale']);

        $values = $form->getData()->getValues();
        foreach (array_keys($data['values']) as $key) {
            $value = $values->get($key);
            if ($value->getAttribute()->isLocalizable()) {
                $value->setLocale($locale);
            }
            if ($key !== $correctKey = ProductValueKeyGenerator::getKey($value)) {
                $data['values'][$correctKey] = $data['values'][$key];
                unset($data['values'][$key]);
            }
        }

        foreach ($data['values'] as $key => $changes) {
            if (isset($changes['media']['file']) && $changes['media']['file'] instanceof UploadedFile) {
                $media = $this->factory->createMedia($changes['media']['file']);
                $this->mediaManager->handle($media, 'product-draft-' . md5(time() . uniqid()));

                $data['values'][$key]['media']['filename'] = $media->getFilename();
                $data['values'][$key]['media']['originalFilename'] = $media->getOriginalFilename();
                $data['values'][$key]['media']['filePath'] = $this->mediaManager->getFilePath($media);
                $data['values'][$key]['media']['mimeType'] = $media->getMimeType();
                $data['values'][$key]['media']['size'] = $changes['media']['file']->getClientSize();

                unset($data['values'][$key]['media']['file']);
            }
        }

        $this->collector->setData($data);
    }
}
