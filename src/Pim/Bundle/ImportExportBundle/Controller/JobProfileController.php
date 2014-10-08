<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use Pim\Bundle\ImportExportBundle\Factory\JobInstanceFactory;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Job Profile controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobProfileController extends AbstractDoctrineController
{
    /** @var ConnectorRegistry */
    protected $connectorRegistry;

    /** @var string */
    protected $jobType;

    /** @var string */
    protected $rootDir;

    /** @var string */
    protected $environment;

    /** @var JobInstanceType */
    protected $jobInstanceType;

    /** @var JobInstanceFactory */
    protected $jobInstanceFactory;

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
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param ConnectorRegistry        $connectorRegistry
     * @param string                   $jobType
     * @param string                   $rootDir
     * @param string                   $environment
     * @param JobInstanceType          $jobInstanceType
     * @param JobInstanceFactory       $jobInstanceFactory
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        ConnectorRegistry $connectorRegistry,
        $jobType,
        $rootDir,
        $environment,
        JobInstanceType $jobInstanceType,
        JobInstanceFactory $jobInstanceFactory
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->connectorRegistry = $connectorRegistry;
        $this->jobType           = $jobType;
        $this->rootDir           = $rootDir;
        $this->environment       = $environment;

        $this->jobInstanceType   = $jobInstanceType;
        $this->jobInstanceType->setJobType($this->jobType);

        $this->jobInstanceFactory = $jobInstanceFactory;
    }

    /**
     * Create a job instance
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $jobInstance = $this->jobInstanceFactory->createJobInstance(null, $this->getJobType(), null);
        $form = $this->createForm($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->persist($jobInstance);

                $this->addFlash('success', sprintf('flash.%s.created', $this->getJobType()));

                $url = $this->generateUrl(
                    sprintf('pim_importexport_%s_profile_edit', $this->getJobType()),
                    array('id' => $jobInstance->getId())
                );
                $response = array('status' => 1, 'url' => $url);

                return new Response(json_encode($response));
            }
        }

        return $this->render(
            sprintf('PimImportExportBundle:%sProfile:create.html.twig', ucfirst($this->getJobType())),
            array(
                'form' => $form->createView()
            )
        );
    }

    /**
     * Show a job instance
     *
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_SHOW, new GenericEvent($jobInstance));

        $form = $this->createForm($this->jobInstanceType, $jobInstance, ['disabled' => true]);
        $uploadAllowed = false;
        $uploadForm = null;
        $job = $jobInstance->getJob();
        foreach ($job->getSteps() as $step) {
            if (method_exists($step, 'getReader')) {
                $reader = $step->getReader();
                if ($reader instanceof UploadedFileAwareInterface) {
                    $uploadAllowed = true;
                    $uploadForm = $this->createUploadForm()->createView();
                }
            }
        }

        $validator = $this->getValidator();

        if (null === $template = $job->getShowTemplate()) {
            $template = sprintf('PimImportExportBundle:%sProfile:show.html.twig', ucfirst($this->getJobType()));
        }

        return $this->render(
            $template,
            array(
                'form'             => $form->createView(),
                'jobInstance'      => $jobInstance,
                'violations'       => $validator->validate($jobInstance, array('Default', 'Execution')),
                'uploadViolations' => $validator->validate($jobInstance, array('Default', 'UploadExecution')),
                'uploadAllowed'    => $uploadAllowed,
                'uploadForm'       => $uploadForm,
            )
        );
    }

    /**
     * Edit a job instance
     *
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_EDIT, new GenericEvent($jobInstance));

        $form = $this->createForm($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->persist($jobInstance);

                $this->addFlash(
                    'success',
                    sprintf('flash.%s.updated', $this->getJobType())
                );

                return $this->redirectToShowView($jobInstance->getId());
            }
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::POST_EDIT, new GenericEvent($jobInstance));

        if (null === $template = $jobInstance->getJob()->getEditTemplate()) {
            $template = sprintf('PimImportExportBundle:%sProfile:edit.html.twig', ucfirst($this->getJobType()));
        }

        return $this->render(
            $template,
            array(
                'jobInstance' => $jobInstance,
                'form'        => $form->createView(),
            )
        );
    }

    /**
     * Remove a job
     *
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            if ($request->isXmlHttpRequest()) {
                return new Response('', 404);
            } else {
                return $this->redirectToIndexView();
            }
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_REMOVE, new GenericEvent($jobInstance));

        $this->remove($jobInstance);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToIndexView();
        }
    }

    /**
     * Launch a job
     *
     * @param Request $request
     * @param integer $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function launchAction(Request $request, $id)
    {
        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        $this->eventDispatcher->dispatch(JobProfileEvents::PRE_EXECUTE, new GenericEvent($jobInstance));

        $violations       = $this->getValidator()->validate($jobInstance, array('Default', 'Execution'));
        $uploadViolations = $this->getValidator()->validate($jobInstance, array('Default', 'UploadExecution'));

        $uploadMode = $uploadViolations->count() === 0 ? $this->processUploadForm($jobInstance) : false;

        if ($uploadMode === true || $violations->count() === 0) {
            $jobExecution = new JobExecution();
            $jobExecution
                ->setJobInstance($jobInstance)
                ->setUser($this->getUser()->getUsername());
            $manager = $this->getDoctrine()->getManagerForClass(get_class($jobExecution));
            $manager->persist($jobExecution);
            $manager->flush($jobExecution);
            $instanceCode = $jobExecution->getJobInstance()->getCode();
            $executionId = $jobExecution->getId();
            $pathFinder = new PhpExecutableFinder();

            $cmd = sprintf(
                '%s %s/console akeneo:batch:job --env=%s --email="%s" %s %s %s >> %s/logs/batch_execute.log 2>&1',
                $pathFinder->find(),
                $this->rootDir,
                $this->environment,
                $this->getUser()->getEmail(),
                $uploadMode ? sprintf('-c \'%s\'', json_encode($jobInstance->getJob()->getConfiguration())) : '',
                $instanceCode,
                $executionId,
                $this->rootDir
            );
            // Please note we do not use Symfony Process as it has some problem
            // when executed from HTTP request that stop fast (race condition that makes
            // the process cloning fail when the parent process, i.e. HTTP request, stops
            // at the same time)
            exec($cmd . ' &');

            $this->eventDispatcher->dispatch(JobProfileEvents::POST_EXECUTE, new GenericEvent($jobInstance));

            $this->addFlash('success', sprintf('The %s is running.', $this->getJobType()));

            return $this->redirectToReportView($jobExecution->getId());
        }

        return $this->redirectToShowView($jobInstance->getId());
    }

    /**
     * Process the upload form
     *
     * @param JobInstance $jobInstance
     *
     * @return boolean
     */
    protected function processUploadForm(JobInstance $jobInstance)
    {
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form = $this->createUploadForm();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->get('file')->getData();
                if ($file = $data->getFile()) {
                    $file = $file->move(sys_get_temp_dir(), $file->getClientOriginalName());

                    return $this->configureUploadJob($jobInstance, $file);
                }

                $this->addFlash('error', 'You must select a file to upload');
            }
        }

        return false;
    }

    /**
     * Configure job instance for uploaded file
     *
     * @param JobInstance $jobInstance
     * @param File        $file
     *
     * @return boolean
     */
    protected function configureUploadJob(JobInstance $jobInstance, File $file)
    {
        $success = false;

        $job = $jobInstance->getJob();
        foreach ($job->getSteps() as $step) {
            if (method_exists($step, 'getReader')) {
                $reader = $step->getReader();

                if ($reader instanceof UploadedFileAwareInterface) {
                    $constraints = $reader->getUploadedFileConstraints();
                    $errors = $this->getValidator()->validateValue($file, $constraints);

                    if ($errors->count() !== 0) {
                        foreach ($errors as $error) {
                            $this->addFlash('error', $error->getMessage());
                        }

                        return false;
                    } else {
                        $reader->setUploadedFile($file);
                        $success = true;
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Get a job instance
     *
     * @param integer $id
     * @param boolean $checkStatus
     *
     * @return Job|RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    protected function getJobInstance($id, $checkStatus = true)
    {
        $jobInstance = $this->findOr404('AkeneoBatchBundle:JobInstance', $id);

        // Fixme: should look at the job execution to see the status of a job instance execution
        if ($checkStatus && $jobInstance->getStatus() === JobInstance::STATUS_IN_PROGRESS) {
            throw $this->createNotFoundException(
                sprintf('The %s "%s" is currently in progress', $jobInstance->getType(), $jobInstance->getLabel())
            );
        }

        $job = $this->connectorRegistry->getJob($jobInstance);

        if (!$job) {
            throw $this->createNotFoundException(
                sprintf(
                    'The following %s does not exist anymore. Please check configuration:<br />' .
                    'Connector: %s<br />' .
                    'Type: %s<br />' .
                    'Alias: %s',
                    $this->getJobType(),
                    $jobInstance->getConnector(),
                    $jobInstance->getType(),
                    $jobInstance->getAlias()
                )
            );
        }
        $jobInstance->setJob($job);

        return $jobInstance;
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

    /**
     * Redirect to the index view
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToIndexView()
    {
        return $this->redirectToRoute(sprintf('pim_importexport_%s_profile_index', $this->getJobType()));
    }

    /**
     * Redirect to the show view
     *
     * @param integer $jobId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToShowView($jobId)
    {
        return $this->redirectToRoute(
            sprintf('pim_importexport_%s_profile_show', $this->getJobType()),
            array('id' => $jobId)
        );
    }

    /**
     * Redirect to the report view
     *
     * @param integer $jobId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function redirectToReportView($jobId)
    {
        return $this->redirectToRoute(
            sprintf('pim_importexport_%s_execution_show', $this->getJobType()),
            array('id' => $jobId)
        );
    }

    /**
     * Create file upload form
     *
     * @return Form
     */
    protected function createUploadForm()
    {
        return $this->createForm(new UploadType());
    }
}
