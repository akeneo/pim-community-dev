import {Axis} from '../model/catalog-volume';

const volumesKeyFigures: {[volumeName: string]: string[]} = {
  product: ['count_products', 'count_product_and_product_model_values', 'average_max_product_and_product_model_values'],
  catalog: ['count_channels', 'count_locales'],
  product_structure: [],
  variant_modeling: [],
  reference_entities: [],
  assets: [],
};

const transformVolumesToKeyFigures = (rawCatalogVolumes: any) => {
  let axesCatalogVolumes: Axis[] = [];
  for (const [axisName, catalogVolumes] of Object.entries(volumesKeyFigures)) {
    // fixme: don't add empty axes
    axesCatalogVolumes.push({
      name: axisName,
      catalogVolumes: catalogVolumes.map(volumeName => {
        const rawVolume = rawCatalogVolumes[volumeName];
        return {
          name: volumeName,
          type: rawVolume.type,
          value: rawVolume.value,
        };
      }),
    });
  }

  return axesCatalogVolumes;
};

export {transformVolumesToKeyFigures};
