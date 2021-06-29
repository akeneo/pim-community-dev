<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelLoader
{
    /** @var SimpleFactoryInterface */
    private $factory;

    /** @var ObjectUpdaterInterface */
    private $updater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $saver;

    /** @var Client */
    private $client;

    public function __construct(
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        Client $client
    ) {
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->client = $client;
    }

    /**
     * @throws \Exception
     */
    public function create(array $data = []): ProductModelInterface
    {
        /** @var  ProductModelInterface $productModel */
        $productModel = $this->factory->create();

        $this->updater->update($productModel, $data);
        $errors = $this->validator->validate($productModel);

        if (0 !== $errors->count()) {
            throw new \Exception(
                sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                )
            );
        }

        $this->saver->save($productModel);
        $this->client->refreshIndex();

        return $productModel;
    }
}
