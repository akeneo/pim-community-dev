<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Controller\InternalApi;

use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GetCategoryTreeController extends AbstractController
{
    private CategoryRepositoryInterface $categoryRepository;
    private SecurityFacade $securityFacade;
    private string $template;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        SecurityFacade $securityFacade,
        string $template
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->securityFacade = $securityFacade;
        $this->template = $template;
    }

    public function __invoke(Request $request, $categoryTreeId)
    {
        if (!$this->securityFacade->isGranted('pim_enrich_product_categories_view')) {
            throw new AccessDeniedException();
        }

        $categoryTree = $this->categoryRepository->find($categoryTreeId);
        if (null === $categoryTree) {
            throw new NotFoundHttpException(sprintf('Category %s not found', $categoryTreeId));
        }

        $selectedCategoryIds = $request->get('selected', []);
        $categories = $this->categoryRepository->getCategoriesByIds($selectedCategoryIds);

        $trees = $this->categoryRepository->getFilledTree($categoryTree, $categories);

        return $this->render($this->template, ['trees' => $trees, 'categories' => $categories]);
    }
}
