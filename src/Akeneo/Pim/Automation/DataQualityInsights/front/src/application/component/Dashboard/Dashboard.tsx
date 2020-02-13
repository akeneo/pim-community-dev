import React, {FunctionComponent} from "react";
import Overview from "./Overview/Overview";
import Widgets from "./Widgets/Widgets";

interface DataQualityInsightsDashboardProps {
  timePeriod: string;
  catalogLocale: string;
  catalogChannel: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const Dashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({timePeriod, catalogLocale, catalogChannel, familyCode, categoryCode}) => {
  return (
    <div id="data-quality-insights-activity-dashboard">
      <div className="AknSubsection">
        <Overview catalogLocale={catalogLocale} catalogChannel={catalogChannel} timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>
        <Widgets catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
      </div>
    </div>
  )
};

export default Dashboard;
