<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes the values of a specific entity from all products.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRelatedEntityRemovalCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:remove-related-entity')
            ->setDescription('Removes the values of a specific entity from all products')
            ->addArgument('entityName', InputArgument::REQUIRED, 'The entity name')
            ->addArgument(
                'ids',
                InputArgument::REQUIRED,
                'The list of entity IDs to remove from the products, comma separated'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $productRepository = $this->getContainer()->get('pim_catalog.repository.product');
        $entityName        = $input->getArgument('entityName');
        $ids               = explode(',', $input->getArgument('ids'));

        foreach ($ids as $id) {
            $this->remove($productRepository, $entityName, (int) $id);
        }
    }

    /**
     * Removes an entity value linked to products and returns the IDs the
     * updated products.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param string                     $entityName
     * @param int                        $id
     */
    protected function remove(ProductRepositoryInterface $productRepository, $entityName, $id)
    {
        switch ($entityName) {
            case 'AssociationType':
                $productRepository->cascadeAssociationTypeRemoval($id);
                break;
            case 'Attribute':
                $productRepository->cascadeAttributeRemoval($id);
                break;
            case 'AttributeOption':
                $productRepository->cascadeAttributeOptionRemoval($id);
                break;
            case 'Category':
                $productRepository->cascadeCategoryRemoval($id);
                break;
            case 'Family':
                $productRepository->cascadeFamilyRemoval($id);
                break;
            case 'Group':
                $productRepository->cascadeGroupRemoval($id);
                break;
            case 'Channel':
                $productRepository->cascadeChannelRemoval($id);
                break;
            default:
                throw new \InvalidArgumentException(sprintf(
                    'The entity "%s" is not a product related entity',
                    $entityName
                ));
        }
    }
}
