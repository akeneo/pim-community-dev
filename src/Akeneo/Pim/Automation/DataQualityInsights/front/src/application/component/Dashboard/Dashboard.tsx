import React, {FunctionComponent} from 'react';
import Overview from './Overview/Overview';
import Widgets from './Widgets/Widgets';
import {AxesContextProvider} from '../../context/AxesContext';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

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
          <div id="data-quality-insights-activity-dashboard">
            <div className="AknSubsection">
              <Overview
                catalogLocale={catalogLocale}
                catalogChannel={catalogChannel}
                timePeriod={timePeriod}
                familyCode={familyCode}
                categoryCode={categoryCode}
              />
              <Widgets catalogLocale={catalogLocale} catalogChannel={catalogChannel} />
            </div>
          </div>
        </AxesContextProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export default Dashboard;
