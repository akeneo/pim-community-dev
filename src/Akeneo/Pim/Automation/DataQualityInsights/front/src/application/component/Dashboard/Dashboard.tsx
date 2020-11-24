import React, {FunctionComponent} from 'react';
import {ScoreDistributionSection} from './ScoreDistributionSection';
import Widgets from './Widgets/Widgets';
import {AxesContextProvider} from '../../context/AxesContext';
import {KeyIndicators} from './KeyIndicators/KeyIndicators';
import {AssetCollectionIcon, EditIcon, pimTheme} from 'akeneo-design-system';
import styled, {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {KeyIndicator} from './index';
import {KeyIndicatorsProvider} from '../../context/KeyIndicatorsContext';
import {keyIndicatorsTips} from '../../helper/Dashboard/KeyIndicatorsTips';
import {redirectToProductGridFilteredByKeyIndicator} from '../../../infrastructure/ProductGridRouter';

import {DashboardContextProvider} from '../../context/DashboardContext';
import {TimePeriod} from '../../../domain';
import {QualityScoreEvolutionSection} from './QualityScoreEvolutionSection';

interface DataQualityInsightsDashboardProps {
  timePeriod: TimePeriod;
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
                <Overview>
                  <QualityScoreEvolutionSection
                    locale={catalogLocale}
                    channel={catalogChannel}
                    familyCode={familyCode}
                    categoryCode={categoryCode}
                  />
                  <ScoreDistributionSection
                    catalogLocale={catalogLocale}
                    catalogChannel={catalogChannel}
                    timePeriod={timePeriod}
                    familyCode={familyCode}
                    categoryCode={categoryCode}
                  />
                </Overview>
                {
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
                }
                <Widgets catalogLocale={catalogLocale} catalogChannel={catalogChannel} />
              </div>
            </div>
          </DashboardContextProvider>
        </AxesContextProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

const Overview = styled.div`
  display: flex;
`;

export default Dashboard;
