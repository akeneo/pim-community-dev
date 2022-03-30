import {keyIndicatorDescriptorsCE} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/keyIndicatorDescriptorsCE';
import {KeyIndicatorDescriptors} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/KeyIndicators/KeyIndicators';
import {ProductType} from '@akeneo-pim-community/data-quality-insights/src/domain/Product.interface';
import {KeyIndicatorExtraData} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {EditIcon, SettingsIcon} from 'akeneo-design-system';
import React from 'react';
import {
  redirectToAttributeGridFilteredByFamilyAndKeyIndicator,
  redirectToAttributeGridFilteredByKeyIndicator,
} from '../../../infrastructure';
import {redirectToProductGridFilteredByKeyIndicator} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/ProductGridRouter';

export const keyIndicatorDescriptorsEE: KeyIndicatorDescriptors = {
  ...keyIndicatorDescriptorsCE,
  values_perfect_spelling: {
    titleI18nKey: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.values_perfect_spelling.title',
    followResults: (
      channelCode: string,
      localeCode: string,
      entityType: ProductType,
      familyCode?: string | null,
      categoryId?: string | null,
      rootCategoryId?: string | null
    ) => {
      redirectToProductGridFilteredByKeyIndicator(
        'data_quality_insights_spelling_quality',
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
  attributes_perfect_spelling: {
    titleI18nKey: 'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.attributes_perfect_spelling.title',
    followResults: (
      localeCode: string,
      familyCode?: string | null,
      categoryId?: string | null,
      extraData?: KeyIndicatorExtraData
    ) => {
      if (familyCode) {
        redirectToAttributeGridFilteredByFamilyAndKeyIndicator([familyCode], localeCode);
      } else if (categoryId && extraData) {
        redirectToAttributeGridFilteredByFamilyAndKeyIndicator(extraData.impactedFamilies, localeCode);
      } else {
        redirectToAttributeGridFilteredByKeyIndicator(localeCode);
      }
    },
    icon: <SettingsIcon />,
  },
};
