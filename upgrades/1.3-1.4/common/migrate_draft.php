<?php

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use PimEnterprise\Bundle\WorkflowBundle\PimEnterpriseWorkflowBundle;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__.'/../../../app/bootstrap.php.cache';
require_once __DIR__.'/../../../app/AppKernel.php';

class Migration
{
    const PIMEE_WORKFLOW_PRODUCT_DRAFT = 'pimee_workflow_product_draft';
    const PIMEE_WORKFLOW_PRODUCT_DRAFT_CLASS = '\PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft';

    protected $output;
    protected $env;
    protected $container;
    protected $kernel;
    protected $errors = ['drafts' => [], 'attributes' => [], 'exceptions' => []];
    protected $mongo = false;

    public function __construct(ConsoleOutput $output, ArgvInput $input)
    {
        $this->output = $output;

        $env = $input->getParameterOption(['-e', '--env']);
        if (!$env) {
            $env = 'dev';
        }

        $this->kernel($env);
    }

    public function execute()
    {
        $drafts = $this->getDrafts();
        if (empty($drafts)) {
            $this->output->writeln('<info>There is no draft to update<info>');

            return;
        }

        $fieldNameBuilder = $this->get('pim_transform.builder.field_name');
        if ($this->mongo) {
            $newStructure = $this->convertMongo($drafts, $fieldNameBuilder);
        } else {
            $newStructure = $this->convertORM($drafts, $fieldNameBuilder);
        }

        $count = count($newStructure);
        if (0 === $count && 0 === $this->errors) {
            $this->output->writeln('<info>There is no draft to update<info>');

            return;
        }

        // new attributes have been founded
        if (0 !== count($this->errors['attributes'])) {
            $this->output->writeln(sprintf(
                "<error>%d draft(s) contain(s) unknown attributes and cannot be converted</error>\nIt seems you have created some attributes which we cannot convert:\n- %s",
                count($this->errors['attributes']),
                implode("\n- ", array_keys($this->errors['attributes']))
            ));

            $this->output->writeln("You can override Migration::convertValues() and add those attributes to insert your converters.\n");
        }

        // exceptions during conversion
        if (0 !== count($this->errors['exceptions'])) {
            $this->output->writeln('<error>Some errors have been detected</error>');
            foreach ($this->errors['exceptions'] as $exception) {
                $this->output->writeln($exception);
            }
        }

        // remove drafts with errors
        foreach ($this->errors['drafts'] as $id => $i) {
            unset($newStructure[$id]);
        }

        if (0 !== $count = count($newStructure)) {
            $this->output->writeln(sprintf('<info>%d draft(s) have to be updated</info>', $count));

            foreach ($newStructure as $id => $structure) {
                $this->update($id, $structure);
            }

            $this->output->writeln('<info>Done !</info>');
        } else {
            $this->output->writeln("<info>No draft to update<info>");
        }
    }

    /**
     * @param int $id
     * @param array $changes
     */
    protected function update($id, $changes)
    {
        if ($this->mongo) {
            $manager = $this->get('pim_catalog.object_manager.product');
            $draft = $manager->find(self::PIMEE_WORKFLOW_PRODUCT_DRAFT_CLASS, $id);
            $draft->setChanges(['values' => $changes]);

            $manager->persist($draft);
            $manager->flush();
        } else {
            $sql = sprintf("UPDATE %s SET changes=:changes WHERE id = :id", self::PIMEE_WORKFLOW_PRODUCT_DRAFT);

            $stmt = $this->get('database_connection')->prepare($sql);
            $stmt->bindValue('changes', json_encode(['values' => $changes]));
            $stmt->bindValue('id', $id);
            $stmt->execute();
        }
    }

    /**
     * @param array            $drafts
     * @param FieldNameBuilder $fieldNameBuilder
     *
     * @return array
     */
    protected function convertMongo(array $drafts, FieldNameBuilder $fieldNameBuilder)
    {
        $newStructure = [];
        foreach ($drafts as $draft) {
            $changes = $draft->getChanges();
            foreach ($changes['values'] as $value) {
                try {
                    if (isset($value['__context__'])) {
                        $nameBuilder = $fieldNameBuilder->extractAttributeFieldNameInfos(
                            $this->buildName($value['__context__'])
                        );
                        if (null !== $nameBuilder) {
                            unset($value['__context__']);
                            $newValue = $this->convertValue($value, $nameBuilder['attribute'], $draft->getId());

                            if (null !== $newValue) {
                                $newStructure[$draft->getId()][$nameBuilder['attribute']->getCode()][] = [
                                    'locale' => $nameBuilder['locale_code'],
                                    'scope'  => $nameBuilder['scope_code'],
                                ] + $newValue;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->errors['exceptions'][] = $e->getMessage();
                    $this->errors['drafts'][$draft->getId()] = 1;
                }
            }
        }

        return $newStructure;
    }

    /**
     * @param array            $drafts
     * @param FieldNameBuilder $fieldNameBuilder
     *
     * @return array
     */
    protected function convertORM(array $drafts, FieldNameBuilder $fieldNameBuilder)
    {
        $newStructure = [];
        foreach ($drafts as $draft) {
            $changes = @unserialize($draft['changes']);
            if (false !== $changes) {
                foreach ($changes['values'] as $value) {
                    try {
                        $nameBuilder = $fieldNameBuilder->extractAttributeFieldNameInfos(
                            $this->buildName($value['__context__'])
                        );
                        if (null !== $nameBuilder) {
                            unset($value['__context__']);
                            $newValue = $this->convertValue($value, $nameBuilder['attribute'], $draft['id']);

                            if (null !== $newValue) {
                                $newStructure[$draft['id']][$nameBuilder['attribute']->getCode()][] = [
                                    'locale' => $nameBuilder['locale_code'],
                                    'scope'  => $nameBuilder['scope_code'],
                                ] + $newValue;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->errors['exceptions'][] = $e->getMessage();
                        $this->errors['drafts'][$draft['id']] = 1;
                    }
                }
            }
        }

        return $newStructure;
    }

    /**
     * @param array              $value
     * @param AttributeInterface $attribute
     *
     * @return array|null
     */
    protected function convertValue(array $value, AttributeInterface $attribute, $draftId)
    {
        switch ($attribute->getBackendType()) {
            case 'media':
                $newValue = [
                    'data' => [
                        'originalFilename' => $value['media']['originalFilename'],
                        'filePath'         => $value['media']['filePath'],
                        'filename'         => $value['media']['filename'],
                    ]
                ];
                break;

            case 'options':
                $newValue['values'] = current($value);
                if (!empty($newValue['values'])) {
                    $ids = explode(',', $newValue['values']);
                    $options = $this->getOptions($ids);
                    $codes = [];
                    foreach ($options as $option) {
                        $codes[] = $option->getCode();
                    }
                    $newValue['data'] = $codes;
                }
                break;

            case 'option':
                $newValue['data'] = current($value);
                if (!empty($newValue['data'])) {
                    $ids = explode(',', $newValue['data']);
                    $options = $this->getOptions($ids);
                    $newValue['data'] = current($options)->getCode();
                }
                break;

            case 'prices':
                $newValue['values'] = [];
                foreach ($value as $prices) {
                    foreach ($prices as $price) {
                        $newValue['data'][] = [
                            'currency' => $price['currency'],
                            'data'     => $price['data'],
                        ];
                    }
                }
                break;

            case 'varchar':
            case 'boolean':
            case 'date':
            case 'decimal':
            case 'text':
            case 'metric':
                $newValue['data'] = current($value);
                break;

            default:
                $this->errors['drafts'][$draftId] = 1;
                $this->errors['attributes'][$attribute->getBackendType()] = 1;

                return;
        }

        return $newValue;
    }

    /**
     * @return array
     */
    protected function getDrafts()
    {
        $mongoDBClass = PimEnterpriseWorkflowBundle::DOCTRINE_MONGODB;
        if (class_exists($mongoDBClass)) {
            $this->mongo = true;
            $repo = $this->get('pim_catalog.object_manager.product')->getRepository(self::PIMEE_WORKFLOW_PRODUCT_DRAFT_CLASS);

            return $repo->findAll();
        } else {
            $sql = sprintf('SELECT id, changes, author FROM %s', self::PIMEE_WORKFLOW_PRODUCT_DRAFT);
            $stmt = $this->get('database_connection')->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();
        }
    }

    /**
     * Load kernel
     *
     * @param string $env
     */
    public function kernel($env = 'dev')
    {
        $this->kernel = new AppKernel($env, $env === 'dev');
        $this->kernel->loadClassCache();
        $this->kernel->boot();

        $this->container = $this->kernel->getContainer();
    }

    /**
     * Close connection
     */
    public function close()
    {
        $this->kernel->shutdown();
        $this->get('database_connection')->close();
    }

    /**
     * @param string $attribute
     *
     * @return string
     */
    protected function buildName($attribute)
    {
        $name = $attribute['attribute'];

        if (isset($attribute['locale'])) {
            $name.= '-' . $attribute['locale'];
        }

        if (isset($attribute['scope'])) {
            $name.= '-' . $attribute['scope'];
        }

        return $name;
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return $this->container->get($service);
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    protected function getOptions($ids)
    {
        $repository = $this->get('pim_catalog.repository.attribute_option');

        return $repository->findBy(['id' => $ids]);
    }
}

$migration = new Migration(new ConsoleOutput(), new ArgvInput($argv));
$migration->execute();
$migration->close();
