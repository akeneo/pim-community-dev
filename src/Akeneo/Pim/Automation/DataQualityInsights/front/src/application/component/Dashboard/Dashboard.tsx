import React, {FunctionComponent} from "react";
import Overview from "./Overview/Overview";
import Widgets from "./Widgets/Widgets";
import {AxesContextProvider} from "../../context/AxesContext";

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
    <AxesContextProvider axes={axes}>
      <div id="data-quality-insights-activity-dashboard">
        <div className="AknSubsection">
          <Overview catalogLocale={catalogLocale} catalogChannel={catalogChannel} timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>
          <Widgets catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
        </div>
      </div>
    </AxesContextProvider>
  )
};

export default Dashboard;
