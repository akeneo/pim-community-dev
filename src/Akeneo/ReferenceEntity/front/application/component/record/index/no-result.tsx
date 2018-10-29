import __ from 'akeneoreferenceentity/tools/translator';
import * as React from 'react';

const memo = (React as any).memo;

const NoResult = memo((entityLabel: string) => {
  <div className="AknGridContainer-noData">
    <div className="AknGridContainer-noDataImage" />
    <div className="AknGridContainer-noDataTitle">
      {__('pim_reference_entity.record.no_result.title', {
        entityLabel,
      })}
    </div>
    <div className="AknGridContainer-noDataSubtitle">{__('pim_reference_entity.record.no_result.subtitle')}</div>
  </div>;
});

export default NoResult;
