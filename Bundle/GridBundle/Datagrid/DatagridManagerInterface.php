<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Builder\ListBuilderInterface;
use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;

interface DatagridManagerInterface
{
    /**
     * Set unique name
     *
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * Set entity manager
     *
     * @param EntityManager $entityManager
     * @return void
     */
    public function setEntityManager(EntityManager $entityManager);

    /**
     * Set entity name
     *
     * @param string $entityName
     * @return void
     */
    public function setEntityName($entityName);

    /**
     * Set query entity alias
     *
     * @param string $queryEntityAlias
     * @return void
     */
    public function setQueryEntityAlias($queryEntityAlias);

    /**
     * Set entity hint
     *
     * @param string $entityHint
     * @return void
     */
    public function setEntityHint($entityHint);

    /**
     * Pass identifier field name from configuration
     *
     * @param string $identifier
     * @return void
     */
    public function setIdentifierField($identifier);

    /**
     * @param DatagridBuilderInterface $datagridBuilder
     * @return void
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    /**
     * @param ListBuilderInterface $listBuilder
     * @return void
     */
    public function setListBuilder(ListBuilderInterface $listBuilder);

    /**
     * @return DatagridInterface
     */
    public function getDatagrid();

    /**
     * @param QueryFactoryInterface $queryManager
     * @return void
     */
    public function setQueryFactory(QueryFactoryInterface $queryManager);

    /**
     * @param TranslatorInterface $translator
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator);

    /**
     * @param string $translationDomain
     * @return void
     */
    public function setTranslationDomain($translationDomain);

    /**
     * @param ValidatorInterface $validator
     * @return void
     */
    public function setValidator(ValidatorInterface $validator);

    /**
     * @param Router $router
     * @return void
     */
    public function setRouter(Router $router);

    /**
     * @param RouteGeneratorInterface $routeGenerator
     * @return void
     */
    public function setRouteGenerator(RouteGeneratorInterface $routeGenerator);

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function setParameters(ParametersInterface $parameters);
}
