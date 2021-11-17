import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {
  AddAttributeIcon,
  AssetCollectionIcon,
  CopyIcon,
  EntityIcon,
  FolderIcon,
  FoldersIcon,
  LocaleIcon,
  pimTheme,
  ProductIcon, ProductModelIcon,
  ShopIcon,
  TagIcon
} from "akeneo-design-system";
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {CatalogVolumeMonitoringApp} from './feature';
import {FakePIM} from './FakePIM';
import { Axe, IconsMapping } from "./feature/model/catalog-volume";

const axes: Axe[] = [
  {
    name: 'product',
    volumes: [
      'count_products',
      'count_product_and_product_model_values',
      'average_max_product_and_product_model_values',
    ],
  },
  {
    name: 'catalog',
    volumes: ['count_channels', 'count_locales'],
  },
  {
    name: 'product_structure',
    volumes: [],
  },
  {
    name: 'variant_modeling',
    volumes: [],
  },
  {
    name: 'reference_entities',
    volumes: [],
  },
  {
    name: 'assets',
    volumes: [],
  },
];

const icons: IconsMapping = {
  count_attributes: TagIcon,
  count_categories: FolderIcon,
  count_category_trees: FoldersIcon,
  count_channels: ShopIcon,
  count_families: EntityIcon,
  count_locales: LocaleIcon,
  count_localizable_and_scopable_attributes: TagIcon,
  count_localizable_attributes: TagIcon,
  count_scopable_attributes: TagIcon,
  count_products: ProductIcon,
  count_product_models: ProductModelIcon,
  count_variant_products: CopyIcon,
  count_product_and_product_model_values: ProductIcon,
  count_reference_entity: EntityIcon,
  count_asset_family: AssetCollectionIcon,
  average_max_attributes_per_family: TagIcon,
  average_max_options_per_attribute: AddAttributeIcon,
  average_max_product_and_product_model_values: ProductIcon,
  average_max_records_per_reference_entity: ProductIcon,
  average_max_attributes_per_reference_entity: TagIcon,
  average_max_assets_per_asset_family: ProductIcon,
  average_max_attributes_per_asset_family: TagIcon,
};

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
        <FakePIM>
          <CatalogVolumeMonitoringApp axes={axes} iconsMapping={icons}/>
        </FakePIM>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
