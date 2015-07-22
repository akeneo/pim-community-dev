<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Update a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $updatesExample = [
            [
                'type'  => 'set_value',
                'field' => 'name',
                'value' => 'My name'
            ],
            [
                'type'        => 'copy_value',
                'from_field'  => 'description',
                'from_scope'  => 'ecommerce',
                'from_locale' => 'en_US',
                'to_field'    => 'description',
                'to_scope'    => 'mobile',
                'to_locale'   => 'en_US'
            ]
        ];

        $this
            ->setName('pim:product:update')
            ->setDescription('Update a product')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            )
            ->addArgument(
                'json_updates',
                InputArgument::REQUIRED,
                sprintf("The product updates in json, for instance, '%s'", json_encode($updatesExample))
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $product = $this->getProduct($identifier);
        if (!$product) {
            $output->writeln(sprintf('<error>product with identifier "%s" not found</error>', $identifier));

            return;
        }

        $updates = json_decode($input->getArgument('json_updates'), true);
        $this->update($product, $updates);

        $violations = $this->validate($product);
        foreach ($violations as $violation) {
            $output->writeln(sprintf("<error>%s</error>", $violation->getMessage()));
        }
        if (0 !== $violations->count()) {
            $output->writeln(sprintf('<error>product "%s" is not valid</error>', $identifier));

            return;
        }

        $this->save($product);
        $output->writeln(sprintf('<info>product "%s" has been updated</info>', $identifier));
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
     * @param ProductInterface $product
     * @param array            $updates
     *
     * @return boolean
     */
    protected function update(ProductInterface $product, array $updates)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['type']);
        $resolver->setAllowedValues(['type' => ['set_value', 'copy_value']]);
        $resolver->setOptional(
            [
                'field',
                'value',
                'locale',
                'scope',
                'from_field',
                'to_field',
                'from_locale',
                'to_locale',
                'from_scope',
                'to_scope'
            ]
        );
        $resolver->setDefaults(
            [
                'locale'      => null,
                'scope'       => null,
                'from_locale' => null,
                'to_locale'   => null,
                'from_scope'  => null,
                'to_scope'    => null
            ]
        );

        foreach ($updates as $update) {
            $update = $resolver->resolve($update);
            if ('set_value' === $update['type']) {
                $this->applySetValue($product, $update);
            } else {
                $this->applyCopyValue($product, $update);
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param array            $update
     */
    protected function applySetValue(ProductInterface $product, array $update)
    {
        $updater = $this->getContainer()->get('pim_catalog.updater.product');
        $updater->setValue(
            [$product],
            $update['field'],
            $update['value'],
            $update['locale'],
            $update['scope']
        );
    }

    /**
     * @param ProductInterface $product
     * @param array            $update
     */
    protected function applyCopyValue(ProductInterface $product, array $update)
    {
        $updater = $this->getContainer()->get('pim_catalog.updater.product');
        $updater->copyValue(
            [$product],
            $update['from_field'],
            $update['to_field'],
            $update['from_locale'],
            $update['to_locale'],
            $update['from_scope'],
            $update['to_scope']
        );
    }

    /**
     * @param ProductInterface $product
     *
     * @return ConstraintViolationListInterface
     */
    protected function validate(ProductInterface $product)
    {
        $validator = $this->getContainer()->get('pim_validator');
        $errors = $validator->validate($product);

        return $errors;
    }

    /**
     * @param ProductInterface $product
     */
    protected function save(ProductInterface $product)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.product');
        $saver->save($product);
    }
}
