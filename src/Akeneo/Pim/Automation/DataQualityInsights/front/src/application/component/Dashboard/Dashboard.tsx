import React, {FunctionComponent} from 'react';
import {pimTheme} from 'akeneo-design-system';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ScoreDistributionSection, Widgets} from '@akeneo-pim-community/data-quality-insights/src/application/component/Dashboard';
import {AxesContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/AxesContext';
import {keyIndicatorsTips} from '@akeneo-pim-community/data-quality-insights/src/application/helper/Dashboard/KeyIndicatorsTips';
import {KeyIndicatorsProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/KeyIndicatorsContext';
import {EEKeyIndicatorsTips} from '../../helper/Dashboard/EEKeyIndicatorsTips';
import {DashboardContextProvider} from '@akeneo-pim-community/data-quality-insights/src/application/context/DashboardContext';
import {TimePeriod} from '@akeneo-pim-community/data-quality-insights/src/domain';
import {PimEnterpriseKeyIndicators} from './PimEnterpriseKeyIndicators';

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
                <ScoreDistributionSection
                  catalogLocale={catalogLocale}
                  catalogChannel={catalogChannel}
                  timePeriod={timePeriod}
                  familyCode={familyCode}
                  categoryCode={categoryCode}
                />

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

export {Dashboard};
