<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class UpdateUserMapping implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $reflectedClass = new \ReflectionClass($classMetadata->getName());
        if (!$reflectedClass->implementsInterface(UserInterface::class)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => 'assetDelayReminder',
            'type' => 'integer',
            'options' => [
                'default' => 5
            ],
        ]);

        $classMetadata->mapField([
            'fieldName' => 'proposalsToReviewNotification',
            'type' => 'boolean',
            'options' => [
                'default' => true
            ],
        ]);

        $classMetadata->mapField([
            'fieldName' => 'proposalsStateNotification',
            'type' => 'boolean',
            'options' => [
                'default' => true
            ],
        ]);

        $classMetadata->mapManyToOne([
            'targetEntity' => CategoryInterface::class,
            'fieldName' => 'defaultAssetTree',
        ]);
    }
}
