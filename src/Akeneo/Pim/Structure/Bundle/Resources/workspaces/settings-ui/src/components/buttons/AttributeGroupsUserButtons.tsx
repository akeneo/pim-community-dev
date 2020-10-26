import React, {FC} from 'react';
import {PimView} from '@akeneo-pim-community/legacy-bridge';

const AttributeGroupsUserButtons: FC = () => {
  return (
    <PimView
      className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
      viewName="pim-attribute-group-index-user-navigation"
    />
  );
};

export {AttributeGroupsUserButtons};
