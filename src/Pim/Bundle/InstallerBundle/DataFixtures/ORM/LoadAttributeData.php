<?php

namespace Pim\Bundle\InstallerBundle\DataFixtures\ORM;

use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttributeTranslation;

/**
 * Load fixtures for Product attributes
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LoadAttributeData extends AbstractInstallerFixture
{
    /**
     * Get entity manager
     * @return Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager
     */
    protected function getProductManager()
    {
        return $this->container->get('pim_catalog.manager.product');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configuration = Yaml::parse(realpath($this->getFilePath()));

        if (isset($configuration['attributes'])) {
            foreach ($configuration['attributes'] as $code => $data) {
                $attribute = $this->createAttribute($code, $data);
                $this->validate($attribute, $data);
                $manager->persist($attribute);
                $this->addReference(get_class($attribute).'.'.$attribute->getCode(), $attribute);
            }
        }

        $manager->flush();
    }

    /**
     * Create a new attribute
     *
     * @param string $code
     * @param array  $data
     *
     * @return ProductAttributeInterface
     */
    public function createAttribute($code, $data)
    {
        $attribute = $this->getProductManager()->createAttribute($data['type']);
        $attribute->setCode($code);

        if (isset($data['group'])) {
            $attribute->setGroup($this->getReference('Pim\Bundle\CatalogBundle\Entity\AttributeGroup.'.$data['group']));
        }

        foreach ($data['labels'] as $locale => $label) {
            $translation = $this->createTranslation($attribute, $locale, $label);
            $attribute->addTranslation($translation);
        }

        if (isset($data['options'])) {
            $options = $this->prepareOptions($code, $data['options']);
            foreach ($options as $option) {
                $attribute->addOption($option);
            }
        }

        $attribute->setParameters($this->prepareParameters($code, $data));

        return $attribute;
    }

    /**
     * Create a translation entity
     *
     * @param ProductAttributeInterface $attribute ProductAttributeInterface entity
     * @param string                    $locale    Locale used
     * @param string                    $content   Translated content
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttributeTranslation
     */
    public function createTranslation($attribute, $locale, $content)
    {
        $translation = new ProductAttributeTranslation();
        $translation->setForeignKey($attribute);
        $translation->setLocale($locale);
        $translation->setLabel($content);

        return $translation;
    }

    /**
     * Prepare parameters
     *
     * @param array $data
     *
     * @return array
     */
    public function prepareParameters($attributeCode, $data)
    {
        $parameters = $data['parameters'];
        $parameters['dateMin'] = (isset($parameters['dateMin'])) ? new \DateTime($parameters['dateMin']) : null;
        $parameters['dateMax'] = (isset($parameters['dateMax'])) ? new \DateTime($parameters['dateMax']) : null;

        if ($data['type'] === 'pim_catalog_simpleselect' and isset($parameters['defaultValue'])) {
            $parameters['defaultValue'] = $this->getReference($this->getOptionReference($attributeCode, $parameters['defaultValue']));
        }

        if (isset($parameters['availableLocales'])) {
            $parameters['availableLocales'] = array_map(
                function ($localeCode) {
                    return $this->getReference('locale.' . $localeCode);
                },
                $parameters['availableLocales']
            );
        }

        return $parameters;
    }

    /**
     * Prepare attribute options
     *
     * @param array $data the options data
     *
     * @return array
     */
    public function prepareOptions($attributeCode, $data)
    {
        $options = array();
        foreach ($data as $code => $optionData) {
            $option = $this->getProductManager()->createAttributeOption();
            $option->setCode($code);
            $option->setTranslatable(true);
            $labels = $optionData['labels'];
            foreach ($labels as $locale => $translated) {
                $optionValue = $this->getProductManager()->createAttributeOptionValue();
                $optionValue->setValue($translated);
                $optionValue->setLocale($locale);
                $option->addOptionValue($optionValue);
            }
            $options[] = $option;
            $this->addReference($this->getOptionReference($attributeCode, $code), $option);
        }

        return $options;
    }

    protected function getOptionReference($attributeCode, $code)
    {
        return 'Pim\Bundle\CatalogBundle\Entity\AttributeOption.' . $attributeCode . '.' . $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 20;
    }
}
