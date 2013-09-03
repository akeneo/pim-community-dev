<?php
namespace Pim\Bundle\CatalogBundle\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Form\FormTypeInterface;

/**
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractDoctrineController extends AbstractController
{
    private $doctrine;
    private $formFactory;
    private $validator;
    
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        RegistryInterface $doctrine,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator
    ) {
        parent::__construct($request, $templating, $router, $securityContext);
        $this->doctrine = $doctrine;
        $this->formFactory = $formFactory;
        $this->validator = $validator;
    }
    
    /**
     * Returns the Doctrine registry service.
     *
     * @return RegistryInterface
     */
    protected function getDoctrine()
    {
        return $this->doctrine;
    }
    
    /**
     * Returns the validator service.
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->validator;
    }
    
    /**
     * Returns the form factory service.
     *
     * @return FormFactoryInterface
     */
    protected function getFormFactory()
    {
        return $this->formFactory;
    }
    
    
    /**
     * Returns the Doctrine manager
     * 
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->doctrine->getManager();
    }
    
    /**
     * @param string $repository
     *
     * @return ObjectRepository
     */
    protected function getRepository($className)
    {
        return $this->getManager()->getRepository($className);
    }
    
    /**
     * Find an entity or throw a 404
     * 
     * @param string  $className Example: 'PimCatalogBundle:Product'
     * @param integer $id
     *
     * @throws NotFoundHttpException
     * @return mixed
     */
    protected function findOr404($className, $id)
    {
        $result = $this->getRepository($className)->find($id);

        if (!$result) {
            throw $this->createNotFoundException(sprintf('%s entity not found', $className));
        }

        return $result;
    }
    
    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type    The built type of the form
     * @param mixed                    $data    The initial data for the form
     * @param array                    $options Options for the form
     *
     * @return Form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * Creates and returns a form builder instance
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormBuilder
     */
    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->formFactory->createBuilder('form', $data, $options);
    }
}
