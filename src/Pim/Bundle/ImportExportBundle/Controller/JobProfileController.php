<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Item\UploadedFileAwareInterface;

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Form\Type\UploadType;
use Pim\Bundle\ImportExportBundle\Form\Type\JobInstanceType;

/**
 * Job Profile controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobProfileController extends AbstractDoctrineController
{
    /**
     * @var ConnectorRegistry
     */
    protected $connectorRegistry;

    /**
     * @var string
     */
    protected $jobType;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var JobInstanceType
     */
    protected $jobInstanceType;

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
     * @param ConnectorRegistry        $connectorRegistry
     * @param string                   $jobType
     * @param string                   $rootDir
     * @param string                   $environment
     * @param JobInstanceType          $jobInstanceType
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
        ConnectorRegistry $connectorRegistry,
        $jobType,
        $rootDir,
        $environment,
        JobInstanceType $jobInstanceType
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

        $this->connectorRegistry = $connectorRegistry;
        $this->jobType           = $jobType;
        $this->rootDir           = $rootDir;
        $this->environment       = $environment;

        $this->jobInstanceType   = $jobInstanceType;
        $this->jobInstanceType->setJobType($this->jobType);
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
        $jobInstance = new JobInstance(null, $this->getJobType(), null);
        $form = $this->createForm($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getManager()->persist($jobInstance);
                $this->getManager()->flush();

                $this->addFlash('success', sprintf('flash.%s.created.', $this->getJobType()));

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

        $uploadAllowed = false;
        $form = null;
        $job = $jobInstance->getJob();
        foreach ($job->getSteps() as $step) {
            if (method_exists($step, 'getReader')) {
                $reader = $step->getReader();
                if ($reader instanceof UploadedFileAwareInterface) {
                    $uploadAllowed = true;
                    $form = $this->createUploadForm()->createView();
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
                'jobInstance'      => $jobInstance,
                'violations'       => $validator->validate($jobInstance, array('Default', 'Execution')),
                'uploadViolations' => $validator->validate($jobInstance, array('Default', 'UploadExecution')),
                'uploadAllowed'    => $uploadAllowed,
                'form'             => $form,
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
        $form = $this->createForm($this->jobInstanceType, $jobInstance);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getManager()->persist($jobInstance);
                $this->getManager()->flush();

                $this->addFlash(
                    'success',
                    sprintf('flash.%s.updated.', $this->getJobType())
                );

                return $this->redirectToShowView($jobInstance->getId());
            }
        }

        return $this->render(
            sprintf('PimImportExportBundle:%sProfile:edit.html.twig', ucfirst($this->getJobType())),
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

        $this->getManager()->remove($jobInstance);
        $this->getManager()->flush();

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

        $violations       = $this->getValidator()->validate($jobInstance, array('Default', 'Execution'));
        $uploadViolations = $this->getValidator()->validate($jobInstance, array('Default', 'UploadExecution'));

        $uploadMode = $uploadViolations->count() === 0 ? $this->processUploadForm($jobInstance) : false;

        if ($uploadMode === true || $violations->count() === 0) {
            $jobExecution = new JobExecution();
            $jobExecution->setJobInstance($jobInstance);
            $this->getManager()->persist($jobExecution);
            $this->getManager()->flush();
            $instanceCode = $jobExecution->getJobInstance()->getCode();
            $executionId = $jobExecution->getId();
            $cmd = sprintf(
                'php %s/console oro:batch:job --env=%s --email="%s" %s %s %s >> %s/logs/batch_execute.log 2>&1',
                $this->rootDir,
                $this->environment,
                $this->getUser()->getEmail(),
                $uploadMode ? sprintf('-c \'%s\'', json_encode($jobInstance->getJob()->getConfiguration())) : '',
                $instanceCode,
                $executionId,
                $this->rootDir
            );
            $process = new Process($cmd);
            $process->start();
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
                $file = $data->getFile();
                $file = $file->move(sys_get_temp_dir(), $file->getClientOriginalName());

                return $this->configureUploadJob($jobInstance, $file);
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
        $jobInstance = $this->findOr404('OroBatchBundle:JobInstance', $id);

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
