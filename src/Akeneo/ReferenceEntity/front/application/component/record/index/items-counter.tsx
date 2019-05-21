import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';

export default React.memo(({count}: {count: number}) => {
  return (
    <div className="AknFilterBox-itemsCounter">
      {__(
        'pim_reference_entity.grid.counter',
        {
          count: count,
        },
        count
      )}
    </div>
  );
});
