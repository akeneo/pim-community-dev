import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';

const EditState = () => (
  <div className="AknTitleContainer-state">
    <div className="updated-status">
      <span className="AknState">{__('pim_common.entity_updated')}</span>
    </div>
  </div>
);

export default EditState;
