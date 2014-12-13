<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupProductTemplate;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Temporary command to import product templates
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportProductTemplateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:import:template');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $referenceProduct = $this->getProduct('AKNTS_BPS');
        $productValues = $referenceProduct->getValues()->toArray();
        $productValuesData = $this->normalize($productValues);

        $variantGroup = $this->getVariantGroup('akeneo_tshirt');
        if ($variantGroup->getProductTemplate()) {
            $template = $variantGroup->getProductTemplate();
        } else {
            $template = new GroupProductTemplate();
        }
        $template->setValuesData($productValuesData);
        $template->setGroup($variantGroup);
        $this->save($template); // TODO : should use cascade on VG

        /** @var Group */
        $variantGroup = $this->getVariantGroup('akeneo_tshirt');
        $readProductTemplate = $variantGroup->getProductTemplate();
        $rawValuesData = $readProductTemplate->getValuesData();
        $values = $this->denormalize($rawValuesData);
        var_dump($values);
    }

    /**
     * @param ProductValueInterface[] $productValues
     *
     * @return array
     */
    protected function normalize(array $productValues)
    {
        // TODO : as for versionning, we should really change for json/structured format
        return $this->getContainer()->get('pim_serializer')->normalize($productValues, 'csv');
    }

    /**
     * @param ProductValueInterface[] $productValues
     *
     * @return array
     */
    protected function denormalize(array $productValues)
    {
        // TODO : as for versionning, we should really change for json/structured format
        return $this->getContainer()->get('pim_serializer')->denormalize($productValues, 'csv');
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');
        $product    = $repository->findOneByIdentifier($identifier);

        return $product;
    }

    /**
     * @param string $code
     *
     * @return Group
     */
    protected function getVariantGroup($code)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.group');
        $group      = $repository->findOneByCode($code);

        return $group;
    }

    /**
     * @param GroupProductTemplate $template
     */
    protected function save(GroupProductTemplate $template)
    {
        $objectManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $objectManager->persist($template);
        $objectManager->flush();
    }
}
