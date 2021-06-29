import React, {FC} from 'react';
import {PimView} from '../../infrastructure/pim-view/PimView';

export const UserButtons: FC = () => (
    <PimView
        className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
        viewName='pim-connectivity-connection-user-navigation'
    />
);
