<?php

namespace AkeneoTestEnterprise\Pim\Enrichment\Category\Integration;

use Akeneo\Pim\Enrichment\Bundle\Form\CategoryFormViewNormalizer;
use Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType;
use Akeneo\Test\Integration\TestCase;

class CategoryFormViewNormalizerIntegration extends TestCase
{
    public function testNormalizeFormView()
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByCode('categoryA');

        $form = $this->get('form.factory')->create(CategoryType::class, $category, []);

        $normalizedForm = $this->get(CategoryFormViewNormalizer::class)->normalizeFormView($form->createView());
        foreach (['label', 'errors', '_token', 'permissions'] as $key) {
            $this->assertArrayHasKey($key, $normalizedForm);
        }

        $locales = ['de_DE', 'en_US', 'fr_FR', 'zh_CN'];
        foreach ($locales as $locale) {
            foreach (['value', 'fullName', 'label'] as $key) {
                $this->assertArrayHasKey($key, $normalizedForm['label'][$locale]);
            }
            $this->assertSame(
                sprintf('pim_category[label][%s]', $locale),
                $normalizedForm['label'][$locale]['fullName']
            );
        }

        $this->assertSame('Category A', $normalizedForm['label']['en_US']['value']);
        $this->assertSame('Catégorie A', $normalizedForm['label']['fr_FR']['value']);

        foreach (['edit', 'view', 'own', 'apply_on_children'] as $key) {
            $this->assertArrayHasKey($key, $normalizedForm['permissions']);
        }

        foreach (['edit', 'view', 'own'] as $permission) {
            foreach (['value', 'fullName', 'choices'] as $key) {
                $this->assertArrayHasKey($key, $normalizedForm['permissions'][$permission]);
            }
            $this->assertIsArray($normalizedForm['permissions'][$permission]['value']);
            $this->assertIsArray($normalizedForm['permissions'][$permission]['choices']);
            $this->assertSame(
                sprintf('pim_category[permissions][%s][]', $permission),
                $normalizedForm['permissions'][$permission]['fullName']
            );
        }

        $this->assertSame(
            'pim_category[permissions][apply_on_children]',
            $normalizedForm['permissions']['apply_on_children']['fullName']
        );

    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
