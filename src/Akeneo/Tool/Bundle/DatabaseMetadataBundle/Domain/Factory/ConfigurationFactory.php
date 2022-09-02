<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfigurationPair;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Description : configure the indexes according to the data source: MySQL or Elasticsearch
 * with the columns, filters, data to order and dates to format
 */
final class ConfigurationFactory
{
    public static function initConfigurationList(): array
    {
        $assetManagerMySql = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_asset_manager_asset',
            'identifier',
            'mysql'
        );
        $assetManagerMySql->setDateFieldName('updated_at');
        $assetManagerMySql->setDataProcessing(DateTimeFormat::formatFromString());
        $assetManagerEs =  EntityIndexConfiguration::create(
            ['identifier','updated_at'],
            'akeneo_assetmanager_asset',
            'identifier',
            'es'
        );
        $assetManagerEs->setDateFieldName('updated_at');
        $assetManagerEs->setDataProcessing(DateTimeFormat::formatFromInt());

        $productMySql = EntityIndexConfiguration::create(
            ['CONCAT("product_",COALESCE(BIN_TO_UUID(uuid), id)) AS id', 'updated'],
            'pim_catalog_product',
            'id',
            'mysql'
        );
        $productMySql->setDateFieldName('updated');
        $productMySql->setDataProcessing(DateTimeFormat::formatFromString());
        $productEs = EntityIndexConfiguration::create(
            ['id','updated'],
            'akeneo_pim_product_and_product_model',
            'id',
            'es'
        );
        $productEs->setDateFieldName('updated');
        $productEs->setDataProcessing(DateTimeFormat::formatFromIso());
        $productEs->setFilterFieldName(sprintf('document_type="%s"', addcslashes(ProductInterface::class, '\\')));

        $productModelMySql = EntityIndexConfiguration::create(
            ['CONCAT("product_model_",id) AS id', 'updated'],
            'pim_catalog_product_model',
            'id',
            'mysql'
        );
        $productModelMySql->setDateFieldName('updated');
        $productModelMySql->setDataProcessing(DateTimeFormat::formatFromString());
        $productModelEs = EntityIndexConfiguration::create(
            ['id','updated'],
            'akeneo_pim_product_and_product_model',
            'id',
            'es'
        );
        $productModelEs->setDateFieldName('updated');
        $productModelEs->setDataProcessing(DateTimeFormat::formatFromIso());
        $productModelEs->setFilterFieldName(sprintf('document_type="%s"', addcslashes(ProductModelInterface::class, '\\')));

        $productProposalMySql = EntityIndexConfiguration::create(
            ['product_id'],
            'pimee_workflow_product_draft',
            'product_id',
            'mysql'
        );
        $productProposalMySql->setFilterFieldName('status = 1');
        $productProposalEs = EntityIndexConfiguration::create(
            ['id'],
            'akeneo_pim_product_proposal',
            'id',
            'es'
        );

        $publishedProductMySql = EntityIndexConfiguration::create(
            ['identifier', 'updated'],
            'pimee_workflow_published_product',
            'identifier',
            'mysql'
        );
        $publishedProductMySql->setDateFieldName('updated');
        $publishedProductMySql->setDataProcessing(DateTimeFormat::formatFromString());
        $publishedProductMyEs = EntityIndexConfiguration::create(
            ['identifier','updated'],
            'akeneo_pim_published_product',
            'identifier',
            'es'
        );
        $publishedProductMyEs->setDateFieldName('updated');
        $publishedProductMyEs->setDataProcessing(DateTimeFormat::formatFromString());

        $referenceEntityMySql = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_reference_entity_record',
            'identifier',
            'mysql'
        );
        $referenceEntityMySql->setDateFieldName('updated_at');
        $referenceEntityMySql->setDataProcessing(DateTimeFormat::formatFromString());
        $referenceEntityEs = EntityIndexConfiguration::create(
            ['identifier','updated_at'],
            'akeneo_referenceentity_record',
            'identifier',
            'es'
        );
        $referenceEntityEs->setDateFieldName('updated_at');
        $referenceEntityEs->setDataProcessing(DateTimeFormat::formatFromInt());

        return [
            'assetManager' => new EntityIndexConfigurationPair($assetManagerMySql, $assetManagerEs),
            'product' => new EntityIndexConfigurationPair($productMySql, $productEs),
            'productModel' => new EntityIndexConfigurationPair($productModelMySql, $productModelEs),
            'productProposal' => new EntityIndexConfigurationPair($productProposalMySql, $productProposalEs),
            'publishedProduct' => new EntityIndexConfigurationPair($publishedProductMySql, $publishedProductMyEs),
            'referenceEntity' => new EntityIndexConfigurationPair($referenceEntityMySql, $referenceEntityEs)
        ];
    }
}
