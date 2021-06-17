<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Form;

use Akeneo\Pim\Enrichment\Bundle\Form\CategoryFormViewNormalizer;
use Akeneo\Pim\Enrichment\Component\Category\Form\CategoryFormViewNormalizerInterface;
use Symfony\Component\Form\FormView;

final class CategoryFormViewNormalizerWithPermissions implements CategoryFormViewNormalizerInterface
{
    private CategoryFormViewNormalizer $categoryFormViewNormalizer;

    public function __construct(CategoryFormViewNormalizer $categoryFormViewNormalizer)
    {
        $this->categoryFormViewNormalizer = $categoryFormViewNormalizer;
    }

    public function normalizeFormView(FormView $formView): array
    {
        $normalizedForView = $this->categoryFormViewNormalizer->normalizeFormView($formView);

        if (isset($formView->children['permissions'])) {
            $normalizedForView['permissions'] = [
                'view' => $this->formatPermissionField($formView->children['permissions']->offsetGet('view')),
                'edit' => $this->formatPermissionField($formView->children['permissions']->offsetGet('edit')),
                'own' => $this->formatPermissionField($formView->children['permissions']->offsetGet('own')),
                'apply_on_children' => [
                    'value' => $formView->children['permissions']->offsetGet('apply_on_children')->vars['value'],
                    'fullName' => $formView->children['permissions']->offsetGet('apply_on_children')->vars['full_name'],
                ],
            ];
        }

        return $normalizedForView;
    }

    private function formatPermissionField(FormView $formView): array
    {
        return [
            'value' => array_values($formView->vars['value']),
            'fullName' => $formView->vars['full_name'],
            'choices'  => array_map(fn ($choice) => ['label' => $choice->label, 'value' => $choice->value], $formView->vars['choices']),
        ];
    }
}
