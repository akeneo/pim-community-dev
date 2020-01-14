import React, {FunctionComponent} from "react";
import DataQualityOverviewHeader from "./DataQualityOverview/DataQualityOverviewHeader";
import DataQualityOverviewCharts from "./DataQualityOverview/DataQualityOverviewCharts";

interface DataQualityInsightsDashboardProps {
  periodicity: string;
  catalogLocale: string;
  catalogChannel: string;
}

const DataQualityInsightsDashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({periodicity, catalogLocale, catalogChannel}) => {
  return (
    <div id="data-quality-insights-activity-dashboard">
      <div className="AknSubsection">
        <DataQualityOverviewHeader periodicity={periodicity}/>
        <DataQualityOverviewCharts catalogLocale={catalogLocale} catalogChannel={catalogChannel} periodicity={periodicity}/>
      </div>
    </div>
  )
};

export default DataQualityInsightsDashboard;
