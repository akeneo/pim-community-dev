<?php

// TO REMOVE

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller;

use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        EngineInterface $templating,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        JobRegistry $jobRegistry,
        JobInstanceFactory $jobInstanceFactory,
        EntityManagerInterface $entityManager,
        JobParametersFactory $jobParametersFactory,
        TranslatorInterface $translator,
        $jobType
    ) {
        $this->jobRegistry = $jobRegistry;
        $this->jobType = $jobType;

        $this->jobInstanceFactory = $jobInstanceFactory;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->templating = $templating;
        $this->entityManager = $entityManager;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->translator = $translator;
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
