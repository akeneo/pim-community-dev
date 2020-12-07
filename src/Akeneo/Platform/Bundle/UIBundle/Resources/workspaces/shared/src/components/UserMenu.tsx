import React, {FC} from 'react';
import {PimView} from '@akeneo-pim-community/legacy-bridge';

const UserMenu: FC = () => {
  return (
    <PimView
      className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
      viewName="pim-menu-user-navigation"
    />
  );
};

export {UserMenu};
