<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;

/**
 * Override of repository factory
 * Repository factory returns only service instead of instanciate repositories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RepositoryFactory extends DefaultRepositoryFactory
{
    /** @var ObjectRepository[] */
    protected $repositoryList = array();

    /** @var array[] */
    protected $repositoryCalls = array();

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $entityName = ltrim($entityName, '\\');

        if (isset($this->repositoryList[$entityName])) {
            return $this->repositoryList[$entityName];
        }

        $repository = $this->createRepository($entityManager, $entityName);

        $this->repositoryList[$entityName] = $repository;

        return $repository;
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager The EntityManager instance.
     * @param string                               $entityName    The name of the entity.
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $metadata            = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName;

        if ($repositoryClassName === null) {
            $configuration       = $entityManager->getConfiguration();
            $repositoryClassName = $configuration->getDefaultRepositoryClassName();
        }

        $repository = new $repositoryClassName($entityManager, $metadata);
        if (isset($this->serviceIds[$entityName])) {
            $this->callDependencyInjection($entityName, $repository);
        }

        return $repository;
    }

    public function addServiceId($entityName, $methodCalls)
    {
        $this->serviceIds[$entityName] = $methodCalls;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function callDependencyInjection($entityName, ObjectRepository $repository)
    {
        if (isset($this->serviceIds[$entityName])) {

            $methodCalls = $this->serviceIds[$entityName];
            foreach ($methodCalls as $methodCall) {
                $method = $methodCall[0];
                $params = isset($methodCall[1]) ? $methodCall[1] : array();
                $params = $this->resolveParameters($params);
                call_user_func_array(array($repository, $method), $params);
            }
        }
    }

    /**
     * Resolve parameter calling the DI
     *
     * @param array $params
     *
     * @return array
     */
    protected function resolveParameters(array $params = array())
    {
        foreach ($params as $key => $param) {
            if ($this->container->hasParameter($param)) {
                $params[$key] = $this->container->getParameter($param);
            } elseif ($this->container->has($param)) {
                $params[$key] = $this->container->get($param);
            }
        }

        return $params;
    }
}
