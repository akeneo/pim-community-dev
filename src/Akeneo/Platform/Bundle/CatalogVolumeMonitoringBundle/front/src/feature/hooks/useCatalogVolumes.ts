import {useState} from 'react';
import {Volume, AverageMaxValue, CounterValue} from "../model/catalog-volume";

type CatalogVolumes = Volume[]; // fixme rename?

const volumesKeyFigures: {[volumeName: string]: string[]} = {
  'product': [
    'count_products',
    'count_product_and_product_model_values',
    'average_max_product_and_product_model_values',
  ],
  'catalog': [
    'count_channels',
    'count_locales'
  ],
  'product_structure': [],
  'variant_modeling': [],
  'reference_entities': [],
  'assets': [],
};

// fixme maybe no need type?
type RawCatalogVolume = {
    value: AverageMaxValue | CounterValue;
    has_warning?: boolean; // @deprecated
    type: string;
};

const useCatalogVolumes = () => {
  const rawCatalogVolumes: {[volumeName: string]: RawCatalogVolume} = {
    count_attributes: {value: 77, has_warning: false, type: 'count'},
    count_categories: {value: 168, has_warning: false, type: 'count'},
    count_category_trees: {value: 4, has_warning: false, type: 'count'},
    count_channels: {value: 3, has_warning: false, type: 'count'},
    count_families: {value: 17, has_warning: false, type: 'count'},
    count_locales: {value: 3, has_warning: false, type: 'count'},
    count_localizable_and_scopable_attributes: {value: 2, has_warning: false, type: 'count'},
    count_localizable_attributes: {value: 10, has_warning: false, type: 'count'},
    count_scopable_attributes: {value: 1, has_warning: false, type: 'count'},
    count_products: {value: 1239, has_warning: false, type: 'count'},
    count_product_models: {value: 81, has_warning: false, type: 'count'},
    count_variant_products: {value: 240, has_warning: false, type: 'count'},
    count_product_and_product_model_values: {value: 0, has_warning: false, type: 'count'},
    average_max_attributes_per_family: {
      value: {average: 14, max: 33},
      has_warning: false,
      type: 'average_max',
    },
    average_max_options_per_attribute: {
      value: {average: 0, max: 0},
      has_warning: false,
      type: 'average_max',
    },
    average_max_product_and_product_model_values: {
      value: {average: 0, max: 0},
      has_warning: false,
      type: 'average_max',
    },
  };

  // fixme: to improve and simplify
  let axesCatalogVolumes: Volume[] = [];
  for (const [volumeName, keyFigures] of Object.entries(volumesKeyFigures)) {
    // fixme: don't add empty axes
    axesCatalogVolumes.push({
      name: volumeName,
      keyFigures: keyFigures.map(volumeName => {
        const rawVolume = rawCatalogVolumes[volumeName]
        return {
          name: volumeName,
          type: rawVolume.type,
          value: rawVolume.value,
        };
      }),
    });
  }

  const [catalogVolumes, setCatalogVolumes] = useState<CatalogVolumes>(axesCatalogVolumes);

  return [catalogVolumes, setCatalogVolumes] as const;
};

export {useCatalogVolumes};
