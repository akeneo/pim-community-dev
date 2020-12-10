import React, {FunctionComponent} from 'react';
import {pimTheme} from 'akeneo-design-system';
import styled, {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {
  ScoreDistributionSection,
  Widgets,
} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {EEKeyIndicatorsTips} from '../../helper/Dashboard/EEKeyIndicatorsTips';
import {DashboardContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/DashboardContext';
import {TimePeriod} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {PimEnterpriseKeyIndicators} from './PimEnterpriseKeyIndicators';
import {QualityScoreEvolutionSection} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard/QualityScoreEvolutionSection';

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

                <KeyIndicatorsProvider tips={{...EEKeyIndicatorsTips, ...keyIndicatorsTips}}>
                  {
                    <PimEnterpriseKeyIndicators
                      family={familyCode}
                      category={categoryCode}
                      locale={catalogLocale}
                      channel={catalogChannel}
                    />
                  }
                </KeyIndicatorsProvider>

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

export {Dashboard};
