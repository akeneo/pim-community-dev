import React, {FunctionComponent} from "react";
import DataQualityOverviewHeader from "./DataQualityOverview/DataQualityOverviewHeader";
import DataQualityOverviewCharts from "./DataQualityOverview/DataQualityOverviewCharts";

interface DataQualityInsightsDashboardProps {
  periodicity: string;
  catalogLocale: string;
  catalogChannel: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const DataQualityInsightsDashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({periodicity, catalogLocale, catalogChannel, familyCode, categoryCode}) => {
  return (
    <div id="data-quality-insights-activity-dashboard">
      <div className="AknSubsection">
        <DataQualityOverviewHeader periodicity={periodicity} familyCode={familyCode} categoryCode={categoryCode}/>
        <DataQualityOverviewCharts catalogLocale={catalogLocale} catalogChannel={catalogChannel} periodicity={periodicity} familyCode={familyCode} categoryCode={categoryCode}/>
      </div>
    </div>
  )
};

export default DataQualityInsightsDashboard;
