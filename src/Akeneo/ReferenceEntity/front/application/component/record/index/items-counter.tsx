import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';

export default ({matchesCount}: {matchesCount: number}) => {
  return (
    <div className="AknFilterBox-filterContainer">
      <div className="AknFilterBox-itemsCounter">
        {__(
          'pim_reference_entity.record.grid.filter.counter',
          {
            matchesCount: matchesCount,
          },
          matchesCount
        )}
      </div>
    </div>
  );
};
