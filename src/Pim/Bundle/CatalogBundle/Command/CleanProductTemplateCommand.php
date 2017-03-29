<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Component\Catalog\AttributeTypes;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clean product templates after attribute/locale/scope/currency deletion
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CleanProductTemplateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product-template:clean-values')
            ->setDescription('Clean removed elements from product templates');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productTemplateRepository = $this->getContainer()->get('pim_catalog.repository.product_template');
        $attributeRepository = $this->getContainer()->get('pim_catalog.repository.cached_attribute');
        $objectManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $serializer = $this->getContainer()->get('pim_serializer');
        $localeCodes = $this->getContainer()->get('pim_catalog.repository.locale')->getActivatedLocaleCodes();
        $channels = $this->getContainer()->get('pim_catalog.repository.channel')->getFullChannels();
        $channelCodes = $this->getContainer()->get('pim_catalog.repository.channel')->getChannelCodes();
        $currencies = $this->getContainer()->get('pim_catalog.repository.currency')->getActivatedCurrencyCodes();
        $objectDetacher = $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher');

        $output->writeln('<info>Start of the cleaning process<info>');
        $productTemplates = $productTemplateRepository->findAll();

        $channelLocales = [];
        foreach ($channels as $channel) {
            $channelLocales[$channel->getCode()] = $channel->getLocaleCodes();
        }

        $processedTemplates = [];
        foreach ($productTemplates as $productTemplate) {
            $cleanData = [];
            foreach ($productTemplate->getValuesData() as $attributeCode => $values) {
                $attribute = $attributeRepository->findOneByIdentifier($attributeCode);
                if ($attribute->isLocaleSpecific()) {
                    $values = array_filter($values, function ($value) use ($attribute) {
                        return in_array($value['locale'], $attribute->getLocaleSpecificCodes());
                    });
                }

                if ($attribute->isScopable()) {
                    $values = array_filter($values, function ($value) use ($channelCodes) {
                        return in_array($value['scope'], $channelCodes);
                    });
                }

                if ($attribute->isLocalizable()) {
                    $values = array_filter($values, function ($value) use ($localeCodes, $channelLocales, $attribute) {
                        $valueLocales = $attribute->isScopable() ? $channelLocales[$value['scope']] : $localeCodes;
                        return in_array($value['locale'], $valueLocales);
                    });
                }

                if (AttributeTypes::PRICE_COLLECTION === $attribute->getAttributeType()) {
                    $values = array_map(function ($value) use ($currencies) {
                        $value['data'] = array_filter($value['data'], function ($data) use ($currencies) {
                            return in_array($data['currency'], $currencies);
                        });

                        return $value;
                    }, $values);
                }

                $cleanData[$attributeCode] = $values;
            }

            $productTemplate->setValuesData($cleanData);
            $objectManager->persist($productTemplate);

            $processedTemplates[] = $productTemplate;
            if (0 === (count($processedTemplates) % 100)) {
                $objectManager->flush();
                $objectDetacher->detachAll($processedTemplates);
                $processedTemplates = [];
            }
        }

        $objectManager->flush();
        $output->writeln('<info>Product templates well cleaned<info>');
    }
}
