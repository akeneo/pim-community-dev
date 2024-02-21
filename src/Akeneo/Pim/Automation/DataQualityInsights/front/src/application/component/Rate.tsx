import React, {FunctionComponent} from 'react';

interface RateProps {
  value?: string | null;
  isLoading?: boolean;
}

const Rate: FunctionComponent<RateProps> = ({value, isLoading = false}) => {
  if (isLoading) {
    return (
      <div className={'AknDataQualityInsightsRate AknDataQualityInsightsRate-Loading'}>
        <img
          src={'bundles/akeneodataqualityinsights/images/AxisRateLoader.svg'}
          className={' AknDataQualityInsightsRate-Loader'}
        />
      </div>
    );
  }
  return (
    <>
      {value ? (
        <div className={'AknDataQualityInsightsRate AknDataQualityInsightsRate-' + value}>{value}</div>
      ) : (
        <div className={'AknDataQualityInsightsRate AknDataQualityInsightsRate-noRate'}>N/A</div>
      )}
    </>
  );
};

export default Rate;
