import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const EmptyChartPlaceholder: FC = () => {
  const translate = useTranslate();
  return (
    <>
      <div className="AknAssetPreview-imageContainer">
        <img src={'bundles/pimui/images/illustrations/Project.svg'} alt="illustrations/Project.svg" />
      </div>
      <div className="AknInfoBlock">
        <p>{translate(`akeneo_data_quality_insights.dqi_dashboard.no_data_title`)}</p>
        <p>{translate(`akeneo_data_quality_insights.dqi_dashboard.no_data_subtitle`)}</p>
      </div>
    </>
  );
};

export {EmptyChartPlaceholder};
