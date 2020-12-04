import React, {FunctionComponent} from 'react';
import Overview from './Overview/Overview';
import Widgets from './Widgets/Widgets';
import {AxesContextProvider} from '../../context/AxesContext';
//@todo DAPI-1436
//import {KeyIndicators} from './KeyIndicators/KeyIndicators';
//import {AssetCollectionIcon, EditIcon, pimTheme} from 'akeneo-design-system';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
/*
import {KeyIndicator} from './index';
import {KeyIndicatorsProvider} from '../../context/KeyIndicatorsContext';
import {keyIndicatorsTips} from '../../helper/Dashboard/KeyIndicatorsTips';
import {redirectToProductGridFilteredByKeyIndicator} from '../../../infrastructure/ProductGridRouter';
*/
import {DashboardContextProvider} from '../../context/DashboardContext';

interface DataQualityInsightsDashboardProps {
  timePeriod: string;
  catalogLocale: string;
  catalogChannel: string;
  familyCode: string | null;
  categoryCode: string | null;
  axes: string[];
}

const Dashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({
  timePeriod,
  catalogLocale,
  catalogChannel,
  familyCode,
  categoryCode,
  axes,
}) => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AxesContextProvider axes={axes}>
          <DashboardContextProvider>
            <div id="data-quality-insights-activity-dashboard">
              <div className="AknSubsection">
                <Overview
                  catalogLocale={catalogLocale}
                  catalogChannel={catalogChannel}
                  timePeriod={timePeriod}
                  familyCode={familyCode}
                  categoryCode={categoryCode}
                />
                {/* @todo DAPI-1436
                <KeyIndicatorsProvider tips={keyIndicatorsTips}>
                  <KeyIndicators
                    channel={catalogChannel}
                    locale={catalogLocale}
                    family={familyCode}
                    category={categoryCode}
                  >
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
                  </KeyIndicators>
                </KeyIndicatorsProvider>
                */}
                <Widgets catalogLocale={catalogLocale} catalogChannel={catalogChannel} />
              </div>
            </div>
          </DashboardContextProvider>
        </AxesContextProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default Dashboard;
