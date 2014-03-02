<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\EnrichBundle\Entity\DatagridConfiguration;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

/**
 * Datagrid configuration controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridController extends AbstractDoctrineController
{
    /** @var DatagridManager */
    protected $manager;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param DatagridManager          $manager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridManager $manager
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->manager = $manager;
    }

    /**
     * Display or save datagrid configuration
     *
     * @param Request $request
     * @param string  $alias
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $alias)
    {
        $user    = $this->getUser();
        $columns = $this->getColumnChoices($alias);

        if (null === $configuration = $this->getDatagridConfiguration($alias, $user)) {
            $configuration = new DatagridConfiguration();
            $configuration->setUser($user);
            $configuration->setDatagridAlias($alias);
            $configuration->setColumns(array_keys($columns));
        }

        $form = $this->createForm(
            'pim_catalog_datagrid_configuration',
            $configuration,
            [
                'columns' => $this->sortArrayByArray($columns, $configuration->getColumns()),
                'action'  => $this->generateUrl(
                    'pim_catalog_datagrid_edit',
                    [
                        'alias'      => $alias,
                        'dataLocale' => $request->get('dataLocale')
                    ]
                ),
            ]
        );

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $violations = $this->validator->validate($configuration);
            if ($violations->count()) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            } else {
                $em = $this->getManager();
                $em->persist($configuration);
                $em->flush();
            }

            return $this->redirectToRoute('pim_enrich_product_index', ['dataLocale' => $request->get('dataLocale')]);
        }

        return $this->render('PimEnrichBundle:Datagrid:edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Sort an array by key given an other array values
     *
     * @param array $array
     * @param array $orderArray
     *
     * @return array
     */
    protected function sortArrayByArray(array $array, array $orderArray)
    {
        $ordered = [];
        foreach ($orderArray as $key) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }

        return $ordered + $array;
    }

    /**
     * Get datagrid columns as choices
     *
     * @param string $alias
     *
     * @return array
     */
    protected function getColumnChoices($alias)
    {
        $choices = array();

        $columnsConfig = $this
            ->manager
            ->getDatagrid($alias)
            ->getAcceptor()
            ->getConfig()
            ->offsetGetByPath(sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY));

        if ($columnsConfig) {
            foreach ($columnsConfig as $code => $meta) {
                $choices[$code]= $meta['label'];
            }
        }

        return $choices;
    }

    /**
     * Retrieve datagrid configuration from datagrid alias and user
     *
     * @param string $alias
     * @param User   $user
     *
     * @return null|DatagridConfiguration
     */
    protected function getDatagridConfiguration($alias, User $user)
    {
        return $this
            ->getRepository('PimEnrichBundle:DatagridConfiguration')
            ->findOneBy(
                [
                    'datagridAlias' => $alias,
                    'user'          => $user,
                ]
            );
    }
}
