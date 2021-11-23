import {Axis} from '../model/catalog-volume';

const volumesKeyFigures: {[volumeName: string]: string[]} = {
  product: ['count_products', 'count_product_and_product_model_values', 'average_max_product_and_product_model_values'],
  catalog: ['count_channels', 'count_locales', 'count_category_trees', 'count_categories'],
  product_structure: [
    'count_families',
    'count_attributes',
    'average_max_attributes_per_family',
    'count_scopable_attributes',
    'count_localizable_attributes',
    'count_localizable_and_scopable_attributes',
    'average_max_options_per_attribute',
  ],
  variant_modeling: ['count_variant_products', 'count_product_models'],
  reference_entities: [
    'count_reference_entity',
    'average_max_records_per_reference_entity',
    'average_max_attributes_per_reference_entity',
  ],
  assets: ['count_asset_family', 'average_max_assets_per_asset_family', 'average_max_attributes_per_asset_family'],
};

const transformVolumesToAxis = (rawCatalogVolumes: any): Axis[] => {
  let axesCatalogVolumes: Axis[] = [];
  for (const [axisName, catalogVolumes] of Object.entries(volumesKeyFigures)) {
    const listCatalogVolumes = catalogVolumes.filter(volumeName => rawCatalogVolumes[volumeName] !== undefined);
    axesCatalogVolumes.push({
      name: axisName,
      catalogVolumes: listCatalogVolumes.map(volumeName => {
        return {
          name: volumeName,
          type: rawCatalogVolumes[volumeName].type,
          value: rawCatalogVolumes[volumeName].value,
        };
      }),
    });
  }

  return axesCatalogVolumes.filter(
    axeCatalogVolume => axeCatalogVolume.catalogVolumes && axeCatalogVolume.catalogVolumes.length > 0
  );
};

export {transformVolumesToAxis};
