import __ from 'akeneoreferenceentity/tools/translator';
import * as React from 'react';

const NoResult = ({entityLabel}: {entityLabel: string}) => {
  return (
    <div className="AknGridContainer-noData">
      <div className="AknGridContainer-noDataImage AknGridContainer-noDataImage--reference-entity" />
      <div className="AknGridContainer-noDataTitle">
        {__('pim_reference_entity.record.no_result.title', {
          entityLabel,
        })}
      </div>
      <div className="AknGridContainer-noDataSubtitle">{__('pim_reference_entity.record.no_result.subtitle')}</div>
    </div>
  );
};

export default NoResult;
