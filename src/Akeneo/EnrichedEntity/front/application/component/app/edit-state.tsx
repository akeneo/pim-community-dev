import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';

const EditState = () => (
  <div className="AknTitleContainer-state">
    <div className="updated-status">
      <span className="AknState">{__('pim_enrich.info.entity.updated')}</span>
    </div>
  </div>
);

export default EditState;
