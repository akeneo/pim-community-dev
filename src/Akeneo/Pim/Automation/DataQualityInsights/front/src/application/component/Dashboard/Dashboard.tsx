import React, {FunctionComponent} from "react";
import {AssetCollectionIcon, EditIcon, pimTheme, SettingsIcon} from "akeneo-design-system";
import {ThemeProvider} from "styled-components";
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";
import {
  KeyIndicator,
  KeyIndicators,
  Overview,
  Widgets
} from "@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard";
import {AxesContextProvider} from "@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext";
import {keyIndicatorsTips} from "@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips";
import {KeyIndicatorsProvider} from "@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext";
import {EEKeyIndicatorsTips} from "../../helper/Dashboard/EEKeyIndicatorsTips";
import {redirectToProductGridFilteredByKeyIndicator} from "@akeneo-pim-community/data-quality-insights/src/infrastructure/ProductGridRouter";
import {DashboardContextProvider} from "@akeneo-pim-community/data-quality-insights/src/application/context/DashboardContext";

interface DataQualityInsightsDashboardProps {
  timePeriod: string;
  catalogLocale: string;
  catalogChannel: string;
  familyCode: string | null;
  categoryCode: string | null;
  axes: string[];
}

const Dashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({timePeriod, catalogLocale, catalogChannel, familyCode, categoryCode, axes}) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AxesContextProvider axes={axes}>
          <DashboardContextProvider>
            <div id="data-quality-insights-activity-dashboard">
              <div className="AknSubsection">
                <Overview catalogLocale={catalogLocale} catalogChannel={catalogChannel} timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>

                <KeyIndicatorsProvider tips={{...EEKeyIndicatorsTips, ...keyIndicatorsTips}}>
                  <KeyIndicators channel={catalogChannel} locale={catalogLocale} family={familyCode} category={categoryCode}>
                    <KeyIndicator
                      type="has_image"
                      title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.has_image.title'}
                      resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
                      followResults={(channelCode: string, localeCode: string, familyCode: string|null, categoryId: string|null, rootCategoryId: string|null) => {
                        redirectToProductGridFilteredByKeyIndicator('data_quality_insights_images_quality', channelCode, localeCode, familyCode, categoryId, rootCategoryId);
                      }}
                    >
                      <AssetCollectionIcon/>
                    </KeyIndicator>

                    <KeyIndicator
                      type="values_perfect_spelling"
                      title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.values_perfect_spelling.title'}
                      resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
                      followResults={(channelCode: string, localeCode: string, familyCode: string|null, categoryId: string|null, rootCategoryId: string|null) => {
                        redirectToProductGridFilteredByKeyIndicator('data_quality_insights_spelling_quality', channelCode, localeCode, familyCode, categoryId, rootCategoryId);
                      }}
                    >
                      <EditIcon/>
                    </KeyIndicator>

                    <KeyIndicator
                      type="good_enrichment"
                      title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.good_enrichment.title'}
                      resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.products_to_work_on'}
                      followResults={(channelCode: string, localeCode: string, familyCode: string|null, categoryId: string|null, rootCategoryId: string|null) => {
                        redirectToProductGridFilteredByKeyIndicator('data_quality_insights_enrichment_quality', channelCode, localeCode, familyCode, categoryId, rootCategoryId);
                      }}
                    >
                      <EditIcon/>
                    </KeyIndicator>

                    <KeyIndicator
                      type="attributes_perfect_spelling"
                      title={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.attributes_perfect_spelling.title'}
                      resultsMessage={'akeneo_data_quality_insights.dqi_dashboard.key_indicators.attributes_to_work_on'}
                    >
                      <SettingsIcon/>
                    </KeyIndicator>
                  </KeyIndicators>
                </KeyIndicatorsProvider>

                <Widgets catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
              </div>
            </div>
          </DashboardContextProvider>
        </AxesContextProvider>
      </ThemeProvider>
    </DependenciesProvider>
  )
};

export {Dashboard};
