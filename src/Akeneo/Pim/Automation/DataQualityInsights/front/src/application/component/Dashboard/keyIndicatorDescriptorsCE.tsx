import React from 'react';
import {KeyIndicatorDescriptors} from './KeyIndicators/KeyIndicators';
import {ProductType} from '../../../domain/Product.interface';
import {AssetCollectionIcon, EditIcon} from 'akeneo-design-system';
import {redirectToProductGridFilteredByKeyIndicator} from '../../../infrastructure/ProductGridRouter';

export const keyIndicatorDescriptorsCE: KeyIndicatorDescriptors = {
  has_image: {
    titleI18nKey: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title',
    followResults: (
      channelCode: string,
      localeCode: string,
      entityType: ProductType,
      familyCode?: string | null,
      categoryId?: string | null,
      rootCategoryId?: string | null
    ) => {
      redirectToProductGridFilteredByKeyIndicator(
        'data_quality_insights_images_quality',
        channelCode,
        localeCode,
        entityType,
        familyCode,
        categoryId,
        rootCategoryId
      );
    },
    icon: <AssetCollectionIcon />,
  },
  good_enrichment: {
    titleI18nKey: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title',
    followResults: (
      channelCode: string,
      localeCode: string,
      entityType: ProductType,
      familyCode?: string | null,
      categoryId?: string | null,
      rootCategoryId?: string | null
    ) => {
      redirectToProductGridFilteredByKeyIndicator(
        'data_quality_insights_enrichment_quality',
        channelCode,
        localeCode,
        entityType,
        familyCode,
        categoryId,
        rootCategoryId
      );
    },
    icon: <EditIcon />,
  },
};
