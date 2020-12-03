import React, {FC} from 'react';
import {PimView} from '@akeneo-pim-community/legacy-bridge';

const LocalesUserButtons: FC = () => {
  return (
    <PimView
      className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
      viewName="pim-locale-index-user-navigation"
    />
  );
};

export {LocalesUserButtons};
