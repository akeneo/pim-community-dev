import React, {FunctionComponent} from 'react';

interface RateProps {
  value?: string;
}

const Rate: FunctionComponent<RateProps> = ({value}) => {
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
