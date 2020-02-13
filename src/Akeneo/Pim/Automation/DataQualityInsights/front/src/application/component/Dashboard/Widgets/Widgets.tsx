import React, {FunctionComponent} from "react";
import FamilyWidget from "./FamilyWidget";
import CategoryWidget from "./CategoryWidget";

interface DataQualityWidgetsProps {
  catalogLocale: string;
  catalogChannel: string;
}

const Widgets: FunctionComponent<DataQualityWidgetsProps> = ({catalogChannel, catalogLocale}) => {
  return (
    <div id="data-quality-insights-activity-dashboard-widgets">
      <div className="AknDataQualityInsights-widgetColumn">
        <CategoryWidget catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
      </div>
      <div className="AknDataQualityInsights-widgetColumn">
        <FamilyWidget catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
      </div>
    </div>
  )
};

export default Widgets;
