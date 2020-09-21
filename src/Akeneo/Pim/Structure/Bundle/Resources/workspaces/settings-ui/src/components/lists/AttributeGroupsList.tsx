import React, {FC} from 'react';
import {PimView} from '@akeneo-pim-community/legacy-bridge';


const AttributeGroupsList: FC = () => {
    return (
        <PimView
            className=''
            viewName='pim-attribute-group-index-list'
        />
   );
};

export {AttributeGroupsList};