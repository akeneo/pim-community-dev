<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface;
use Oro\Bundle\NavigationBundle\Entity\Repository\NavigationRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NavigationHistoryController
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var NavigationRepositoryInterface */
    protected $repository;

    /** @var ItemFactory */
    protected $itemFactory;

    /** @var SaverInterface */
    protected $saver;

    /**
     * @param TokenStorageInterface         $tokenStorage
     * @param NavigationRepositoryInterface $repository
     * @param ItemFactory                   $itemFactory
     * @param SaverInterface                $saver
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        NavigationRepositoryInterface $repository,
        ItemFactory $itemFactory,
        SaverInterface $saver
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->repository   = $repository;
        $this->itemFactory  = $itemFactory;
        $this->saver        = $saver;
    }

    /**
     * Saves a history item, by creating a new one if the page is visited for
     * the first time, or by updating an existing one if the user has already
     * visited the page.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postAction(Request $request)
    {
        $history = json_decode($request->getContent(), true);

        $historyItem = $this->findOrCreate($this->getUser(), $history['url']);
        $historyItem->setTitle(json_encode($history['title']));
        $historyItem->doUpdate();

        $this->saver->save($historyItem);

        return new JsonResponse();
    }

    /**
     * @return UserInterface|string
     */
    protected function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * Returns an existing history item, finding it by URL and user,
     * or creates and returns a new one.
     *
     * @param UserInterface $user
     * @param string        $url
     *
     * @return NavigationItemInterface
     */
    protected function findOrCreate(UserInterface $user, $url)
    {
        $historyItem = $this->repository->findOneBy([
            'user' => $user,
            'url'  => $url,
        ]);

        if (null === $historyItem) {
            $historyItem = $this->itemFactory->createItem(
                NavigationHistoryItem::NAVIGATION_HISTORY_ITEM_TYPE,
                [
                    'user' => $user,
                    'url'  => $url,
                ]
            );
        }

        return $historyItem;
    }
}
