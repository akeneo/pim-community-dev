<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Symfony\Form;

use Symfony\Component\Form\FormView;

final class CategoryFormViewNormalizer implements CategoryFormViewNormalizerInterface
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

        // No error mapping for now
        foreach ($formView->vars['errors'] as $error) {
            $formData['errors'][] = $error->getMessage();
        }
        $formData['errors'] = array_unique($formData['errors']);

        return $formData;
    }
}
