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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Subscriber;

use Akeneo\Pim\Automation\SuggestData\Application\Launcher\JobLauncherInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeOptionDeletedSubscriber implements EventSubscriberInterface
{
    public const JOB_INSTANCE_NAME = 'suggest_data_remove_attribute_option_from_mapping';

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /*
     * Ecouter tous les événements POST_REMOVE
     * Ignorer tout ce qui n'est pas un AttributeOptionInterface
     * Récupérer l'attribut de l'option
     * Lancer un job pour traiter les impact sur Franklin en asynchrone
     *      Paramètres :
     *          - AttributeCode
     *          - AttributeOptionCode
     *
     * Job :
     *  - Récupérer tous les codes des familles qui ont l'attribut (cf Query SelectFamilyCodesByAttributeQueryInterface)
     *  - Pour chacune de ces familles
     *      - Récupérer le mapping des attributs de la famille
     *      - Si l'attribut est utilisé dans le mapping
     *          - récupérer le mapping des options de l'attribut (récupérer l'attribute id de Franklin associé)
     *          - Chercher parmi les options s'il y en a une qui a pour ID pim AttributeOptionCode
     *          - Si oui, mettre la valeur à null dans le "to" pour cette option et appeler Franklin
     */

    public function __construct(JobLauncherInterface $jobLauncher)
    {
        $this->jobLauncher = $jobLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostRemove(GenericEvent $event): void
    {
        $attributeOption = $event->getSubject();
        if (!$attributeOption instanceof AttributeOptionInterface) {
            return;
        }

        $this->jobLauncher->launch(self::JOB_INSTANCE_NAME, [
            'pim_attribute_code' => $attributeOption->getAttribute()->getCode(),
            'attribute_option_code' => $attributeOption->getCode(),
        ]);
    }
}
