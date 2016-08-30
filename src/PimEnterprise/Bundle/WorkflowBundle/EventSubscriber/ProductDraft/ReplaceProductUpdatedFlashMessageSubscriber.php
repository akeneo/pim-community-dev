<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\EnrichBundle\Flash\Message;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Replace "product updated" flash by "product draft updated" if necessary
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ReplaceProductUpdatedFlashMessageSubscriber implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ObjectRepository */
    protected $repository;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ObjectRepository              $repository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ObjectRepository $repository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['replaceFlash', 129],
        ];
    }

    /**
     * Replace product flash message if necessary
     *
     * @param FilterResponseEvent $event
     */
    public function replaceFlash(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if (null === $id = $request->attributes->get('id')) {
            return;
        }

        $bag = $event->getRequest()->getSession()->getFlashBag();
        foreach ($bag->peekAll() as $flashes) {
            foreach ($flashes as $flash) {
                if (!$flash instanceof Message) {
                    continue;
                }
                if ('flash.product.updated' === $flash->getTemplate() && !$this->isOwner($id)) {
                    $flash->setTemplate('flash.product_draft.updated');
                }
            }
        }
    }

    /**
     * Wether owner current user is owner of the request product id
     *
     * @param string $id
     *
     * @return bool
     */
    protected function isOwner($id)
    {
        if (null === $product = $this->repository->find($id)) {
            return false;
        }

        return $this->authorizationChecker->isGranted(Attributes::OWN, $product);
    }
}
