import React, {FunctionComponent} from "react";

const __ = require("oro/translator");

interface EmptyChartPlaceholderProps {}

const EmptyChartPlaceholder: FunctionComponent<EmptyChartPlaceholderProps> = () => {
  return (
    <>
      <div className="AknAssetPreview-imageContainer">
        <img src={"bundles/pimui/images/illustrations/Project.svg"} alt="illustrations/Project.svg"/>
      </div>
      <div className="AknInfoBlock">
        <p>{__(`akeneo_data_quality_insights.dqi_dashboard.no_data_title`)}</p>
        <p>{__(`akeneo_data_quality_insights.dqi_dashboard.no_data_subtitle`)}</p>
      </div>
    </>
  );
};

export default EmptyChartPlaceholder;
