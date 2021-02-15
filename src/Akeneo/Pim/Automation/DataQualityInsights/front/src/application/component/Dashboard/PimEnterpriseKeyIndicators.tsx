import React, {FC} from 'react';
import {
  KeyIndicator,
  KeyIndicators,
} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard';
import {redirectToProductGridFilteredByKeyIndicator} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/ProductGridRouter';
import {
  redirectToAttributeGridFilteredByFamilyAndKeyIndicator,
  redirectToAttributeGridFilteredByKeyIndicator,
  useGetSpellcheckSupportedLocales,
} from '../../../infrastructure';
import {AssetCollectionIcon, EditIcon, SettingsIcon} from 'akeneo-design-system';
import {KeyIndicatorExtraData} from '@akeneo-pim-community/data-quality-insights/src/domain';

type Props = {
  channel: string;
  locale: string;
  family: string | null;
  category: string | null;
};

const PimEnterpriseKeyIndicators: FC<Props> = ({channel, locale, family, category}) => {
  const spellcheckSupportedLocales = useGetSpellcheckSupportedLocales();

  if (spellcheckSupportedLocales === null) {
    return <></>;
  }

  return (
    <KeyIndicators channel={channel} locale={locale} family={family} category={category}>
      <KeyIndicator
        type="has_image"
        title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title'}
        resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
        followResults={(
          channelCode: string,
          localeCode: string,
          familyCode: string | null,
          categoryId: string | null,
          rootCategoryId: string | null
        ) => {
          redirectToProductGridFilteredByKeyIndicator(
            'data_quality_insights_images_quality',
            channelCode,
            localeCode,
            familyCode,
            categoryId,
            rootCategoryId
          );
        }}
      >
        <AssetCollectionIcon />
      </KeyIndicator>

      {spellcheckSupportedLocales.includes(locale) && (
        <KeyIndicator
          type="values_perfect_spelling"
          title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.values_perfect_spelling.title'}
          resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
          followResults={(
            channelCode: string,
            localeCode: string,
            familyCode: string | null,
            categoryId: string | null,
            rootCategoryId: string | null
          ) => {
            redirectToProductGridFilteredByKeyIndicator(
              'data_quality_insights_spelling_quality',
              channelCode,
              localeCode,
              familyCode,
              categoryId,
              rootCategoryId
            );
          }}
        >
          <EditIcon />
        </KeyIndicator>
      )}

      <KeyIndicator
        type="good_enrichment"
        title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title'}
        resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
        followResults={(
          channelCode: string,
          localeCode: string,
          familyCode: string | null,
          categoryId: string | null,
          rootCategoryId: string | null
        ) => {
          redirectToProductGridFilteredByKeyIndicator(
            'data_quality_insights_enrichment_quality',
            channelCode,
            localeCode,
            familyCode,
            categoryId,
            rootCategoryId
          );
        }}
      >
        <EditIcon />
      </KeyIndicator>

      {spellcheckSupportedLocales.includes(locale) && (
        <KeyIndicator
          type="attributes_perfect_spelling"
          title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.attributes_perfect_spelling.title'}
          resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.attributes_to_work_on'}
          followResults={(
            // @ts-ignore
            channelCode: string,
            localeCode: string,
            familyCode: string | null,
            // @ts-ignore
            categoryId: string | null,
            // @ts-ignore
            rootCategoryId: string | null,
            extraData: KeyIndicatorExtraData | undefined
          ) => {
            if (familyCode) {
              redirectToAttributeGridFilteredByFamilyAndKeyIndicator([familyCode], localeCode);
            } else if (category && extraData) {
              redirectToAttributeGridFilteredByFamilyAndKeyIndicator(extraData.impactedFamilies, localeCode);
            } else {
              redirectToAttributeGridFilteredByKeyIndicator(localeCode);
            }
          }}
        >
          <SettingsIcon />
        </KeyIndicator>
      )}
    </KeyIndicators>
  );
};

export {PimEnterpriseKeyIndicators};
