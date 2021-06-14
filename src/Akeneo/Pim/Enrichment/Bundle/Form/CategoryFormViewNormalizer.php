<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Form;

use Symfony\Component\Form\FormView;

final class CategoryFormViewNormalizer
{
    public function normalizeFormView(FormView $formView): array
    {
        $formData = ['label' => [], 'errors' => []];

        if (isset($formView->children['label'])) {
            foreach ($formView->children['label']->children as $locale => $labelForm) {
                $formData['label'][$locale] = [
                    'value' => $labelForm->vars['value'],
                    'fullName' => $labelForm->vars['full_name'],
                    'label' => $labelForm->vars['label'],
                ];
            }
        }
        if (isset($formView->children['_token'])) {
            $formData['_token'] = [
                'value' => $formView->children['_token']->vars['value'],
                'fullName' => $formView->children['_token']->vars['full_name'],
            ];
        }

        if (isset($formView->children['permissions'])) {
            $formData['permissions'] = [
                'view' => $this->formatPermissionField($formView->children['permissions']->offsetGet('view')),
                'edit' => $this->formatPermissionField($formView->children['permissions']->offsetGet('edit')),
                'own' => $this->formatPermissionField($formView->children['permissions']->offsetGet('own')),
                'apply_on_children' => [
                    'value' => $formView->children['permissions']->offsetGet('apply_on_children')->vars['value'],
                    'fullName' => $formView->children['permissions']->offsetGet('apply_on_children')->vars['full_name'],
                ],
            ];
        }

        // No error mapping for now
        foreach ($formView->vars['errors'] as $error) {
            $formData['errors'][] = $error->getMessage();
        }
        $formData['errors'] = array_unique($formData['errors']);

        return $formData;
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
