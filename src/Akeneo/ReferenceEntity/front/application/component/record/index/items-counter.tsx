import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';

export default React.memo(({count, inline = false}: {count: number, inline?: boolean}) => {
  return (
    <div className={`AknFilterBox-itemsCounter ${inline ? 'AknFilterBox-itemsCounter--inline' : ''}`}>
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
