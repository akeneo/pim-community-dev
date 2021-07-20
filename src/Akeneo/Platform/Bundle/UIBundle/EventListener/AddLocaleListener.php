<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Aims to inject user context locale into translatable entities, used by views to display relevant titles for family,
 * association type, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddLocaleListener implements EventSubscriber
{
    protected $locale;

    public function getSubscribedEvents(): array
    {
        return [
            'postLoad',
        ];
    }

    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof TranslatableInterface) {
            $entity->setLocale($this->locale);
        }
    }
}
