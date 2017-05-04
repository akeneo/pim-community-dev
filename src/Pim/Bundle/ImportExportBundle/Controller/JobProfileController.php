<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceFormType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Job Profile controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobProfileController
{
    const DEFAULT_CREATE_TEMPLATE = 'PimImportExportBundle:%sProfile:create.html.twig';

    /** @var JobRegistry */
    protected $jobRegistry;

    /** @var string */
    protected $jobType;

    /** @var JobInstanceFormType */
    protected $jobInstanceFormType;

    /** @var JobInstanceFactory */
    protected $jobInstanceFactory;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var RouterInterface */
    protected $router;

    /** @var EngineInterface */
    protected $templating;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /**
     * @param EngineInterface              $templating
     * @param RouterInterface              $router
     * @param FormFactoryInterface         $formFactory
     * @param JobRegistry                  $jobRegistry
     * @param JobInstanceFormType          $jobInstanceFormType
     * @param JobInstanceFactory           $jobInstanceFactory
     * @param EntityManagerInterface       $entityManager
     * @param JobParametersFactory         $jobParametersFactory
     * @param string                       $jobType
     */
    public function __construct(
        EngineInterface $templating,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        JobRegistry $jobRegistry,
        JobInstanceFormType $jobInstanceFormType,
        JobInstanceFactory $jobInstanceFactory,
        EntityManagerInterface $entityManager,
        JobParametersFactory $jobParametersFactory,
        $jobType
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->jobType = $jobType;

        $this->jobInstanceFormType = $jobInstanceFormType;
        $this->jobInstanceFormType->setJobType($this->jobType);

        $this->jobInstanceFactory = $jobInstanceFactory;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->templating = $templating;
        $this->entityManager = $entityManager;
        $this->jobParametersFactory = $jobParametersFactory;
    }

    /**
     * Create a job instance
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $jobInstance = $this->jobInstanceFactory->createJobInstance($this->getJobType());
        $form = $this->formFactory->create($this->jobInstanceFormType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $job = $this->jobRegistry->get($jobInstance->getJobName());
                $jobParameters = $this->jobParametersFactory->create($job);
                $jobInstance->setRawParameters($jobParameters->all());

                $this->entityManager->persist($jobInstance);
                $this->entityManager->flush();

                $request->getSession()->getFlashBag()
                    ->add('success', new Message(sprintf('flash.%s.created', $this->getJobType())));

                $url = $this->router->generate(
                    sprintf('pim_importexport_%s_profile_edit', $this->getJobType()),
                    ['code' => $jobInstance->getCode()]
                );
                $response = ['status' => 1, 'url' => $url];

                return new Response(json_encode($response));
            }
        }

        return $this->templating->renderResponse(
            sprintf(self::DEFAULT_CREATE_TEMPLATE, ucfirst($jobInstance->getType())),
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * Return the job type of the controller
     *
     * @return string
     */
    protected function getJobType()
    {
        return $this->jobType;
    }
}
