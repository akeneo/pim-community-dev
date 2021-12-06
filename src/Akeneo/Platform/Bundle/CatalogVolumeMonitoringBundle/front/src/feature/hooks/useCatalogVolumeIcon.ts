import React from 'react';
import {AkeneoIcon} from 'akeneo-design-system';
import {
  AddAttributeIcon,
  AssetCollectionIcon,
  CopyIcon,
  EntityIcon,
  FolderIcon,
  FoldersIcon,
  LocaleIcon,
  ProductIcon,
  ProductModelIcon,
  ShopIcon,
  TagIcon,
} from 'akeneo-design-system';

const iconsMapping: {[volumeName: string]: any} = {
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

const useCatalogVolumeIcon = (name: string) => {
  return React.createElement(iconsMapping[name] ?? AkeneoIcon, {});
};

export {useCatalogVolumeIcon};
