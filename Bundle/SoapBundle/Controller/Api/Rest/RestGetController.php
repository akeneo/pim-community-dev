<?php

namespace Oro\Bundle\SoapBundle\Controller\Api\Rest;

use Oro\Bundle\SoapBundle\Controller\Api\EntityManagerAwareInterface;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\UnitOfWork;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;

abstract class RestGetController extends FOSRestController implements EntityManagerAwareInterface, RestApiReadInterface
{
    const ITEMS_PER_PAGE = 10;

    /**
     * GET entities list
     *
     * @param  int      $page
     * @param  int      $limit
     * @return Response
     */
    public function handleGetListRequest($page = 1, $limit = self::ITEMS_PER_PAGE)
    {
        $manager = $this->getManager();
        $items = $manager->getList($limit, $page);

        $result = array();
        foreach ($items as $item) {
            $result[] = $this->getPreparedItem($item);
        }
        unset($items);

        return new Response(json_encode($result), Codes::HTTP_OK);
    }

    /**
     * GET single item
     *
     * @param  mixed    $id
     * @return Response
     */
    public function handleGetRequest($id)
    {
        $item = $this->getManager()->find($id);

        if ($item) {
            $item = $this->getPreparedItem($item);
        }
        $responseData = $item ? json_encode($item) : '';

        return new Response($responseData, $item ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * Prepare entity for serialization
     *
     * @param  mixed $entity
     * @return array
     */
    protected function getPreparedItem($entity)
    {
        if ($entity instanceof Proxy && !$entity->__isInitialized()) {
            $entity->__load();
        }
        $result = array();
        if ($entity) {
            /** @var UnitOfWork $uow */
            $uow = $this->getDoctrine()->getManager()->getUnitOfWork();
            foreach ($uow->getOriginalEntityData($entity) as $field => $value) {
                $accessors = array('get' . ucfirst($field), 'is' . ucfirst($field), 'has' . ucfirst($field));
                foreach ($accessors as $accessor) {
                    if (method_exists($entity, $accessor)) {
                        $value = $entity->$accessor();

                        $this->transformEntityField($field, $value);
                        $result[$field] = $value;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Prepare entity field for serialization
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function transformEntityField($field, &$value)
    {
        if ($value instanceof Proxy && method_exists($value, '__toString')) {
            $value = (string) $value;
        } elseif ($value instanceof \DateTime) {
            $value = $value->format('c');
        }
    }
}
