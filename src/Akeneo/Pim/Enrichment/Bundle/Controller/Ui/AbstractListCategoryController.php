<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\Ui;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller used to render categories of an entity (like products or product models).
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractListCategoryController extends AbstractController
{
    protected CategoryRepositoryInterface $categoryRepository;
    protected SecurityFacade $securityFacade;
    protected string $categoryClass;
    protected string $acl;
    protected string $template;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        string $categoryClass,
        string $acl,
        string $template
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->securityFacade = $securityFacade;
        $this->categoryClass = $categoryClass;
        $this->acl = $acl;
        $this->template = $template;
    }

    protected function doListCategoriesAction(Request $request, string $id, string $categoryId): Response
    {
        if (!$this->securityFacade->isGranted($this->acl)) {
            throw new AccessDeniedException();
        }

        $entityWithCategories = $this->findEntityWithCategoriesOr404($id);
        $category = $this->categoryRepository->find($categoryId);

        if (null === $category) {
            throw new NotFoundHttpException(sprintf('%s category not found', $this->categoryClass));
        }

        $categories = null;
        $selectedCategoryCodes = $request->get('selected', null);
        if (null !== $selectedCategoryCodes) {
            $categories = $this->categoryRepository->getCategoriesByCodes($selectedCategoryCodes);
        } elseif (null !== $entityWithCategories) {
            $categories = $entityWithCategories->getCategories();
        }

        $trees = $this->getFilledTree($category, $categories);

        return $this->render($this->template, ['trees' => $trees, 'categories' => $categories]);
    }

    /**
     * Find an entity by its id or return a 404 response
     *
     * @param string $id
     *
     * @return mixed
     *
     * @throws NotFoundHttpException
     */
    abstract protected function findEntityWithCategoriesOr404(string $id);

    /**
     * Fetch the filled tree
     *
     * @param CategoryInterface $parent
     * @param Collection        $categories
     *
     * @return CategoryInterface[]
     */
    protected function getFilledTree(CategoryInterface $parent, Collection $categories): array
    {
        return $this->categoryRepository->getFilledTree($parent, $categories);
    }
}
