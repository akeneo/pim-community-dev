<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Controller;

use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryLabelsController
{
    public function __construct(
        private readonly CategoryQueryInterface $categoryQuery,
        private readonly UserContext $userContext,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $categoryCodes = $request->get('categoryCodes', []);
        Assert::isArray($categoryCodes);
        $userLocale = $this->userContext->getCurrentLocaleCode();

        return new JsonResponse(
            \array_reduce(
                \iterator_to_array($this->categoryQuery->byCodes($categoryCodes)),
                function ($old, $category) use ($userLocale) {
                    $old[$category->getCode()] = $category->getLabels()[$userLocale] ?? \sprintf('[%s]', $category->getCode());

                    return $old;
                },
                []
            )
        );
    }
}
