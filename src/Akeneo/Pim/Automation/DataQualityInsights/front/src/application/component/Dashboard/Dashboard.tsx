import React, {FunctionComponent} from 'react';
import {ScoreDistributionSection} from './ScoreDistributionSection';
import Widgets from './Widgets/Widgets';
import {AxesContextProvider} from '../../context/AxesContext';
import {KeyIndicators} from './KeyIndicators/KeyIndicators';
import {pimTheme} from 'akeneo-design-system';
import styled, {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {KeyIndicatorsProvider} from '../../context/KeyIndicatorsContext';
import {keyIndicatorsTips} from '../../helper/Dashboard/KeyIndicatorsTips';

import {DashboardContextProvider} from '../../context/DashboardContext';
import {TimePeriod} from '../../../domain';
import {QualityScoreEvolutionSection} from './QualityScoreEvolutionSection';
import {keyIndicatorDescriptorsCE} from './keyIndicatorDescriptorsCE';

interface DataQualityInsightsDashboardProps {
  timePeriod: TimePeriod;
  catalogLocale: string;
  catalogChannel: string;
  familyCode: string | null;
  categoryCode: string | null;
  categoryId: string | null;
  rootCategoryId: string | null;
  axes: string[];
}

const Dashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({
  timePeriod,
  catalogLocale,
  catalogChannel,
  familyCode,
  categoryCode,
  categoryId,
  rootCategoryId,
  axes,
}) => {
  const category =
    categoryCode === null || categoryId === null || rootCategoryId === null
      ? null
      : {
          id: categoryId,
          code: categoryCode,
          rootCategoryId,
        };

  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AxesContextProvider axes={axes}>
          <DashboardContextProvider familyCode={familyCode} category={category}>
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
                      keyIndicatorDescriptors={keyIndicatorDescriptorsCE}
                    />
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
