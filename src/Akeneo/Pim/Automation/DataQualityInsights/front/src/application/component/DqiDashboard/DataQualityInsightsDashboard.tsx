import React, {FunctionComponent} from "react";
import DataQualityOverviewHeader from "./DataQualityOverview/DataQualityOverviewHeader";
import DataQualityOverviewCharts from "./DataQualityOverview/DataQualityOverviewCharts";
import DataQualityWidgets from "./Widgets/DataQualityWidgets";

interface DataQualityInsightsDashboardProps {
  timePeriod: string;
  catalogLocale: string;
  catalogChannel: string;
  familyCode: string | null;
  categoryCode: string | null;
}

const DataQualityInsightsDashboard: FunctionComponent<DataQualityInsightsDashboardProps> = ({timePeriod, catalogLocale, catalogChannel, familyCode, categoryCode}) => {
  return (
    <div id="data-quality-insights-activity-dashboard">
      <div className="AknSubsection">
        <DataQualityOverviewHeader timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>
        <DataQualityOverviewCharts catalogLocale={catalogLocale} catalogChannel={catalogChannel} timePeriod={timePeriod} familyCode={familyCode} categoryCode={categoryCode}/>
        <DataQualityWidgets catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
      </div>
    </div>
  )
};

export default DataQualityInsightsDashboard;
