import React, {FC} from 'react';
import {PimView} from '@akeneo-pim-community/legacy-bridge';

const AttributeGroupsIndex: FC = () => {
    return (
        <PimView
            className=''
            viewName='pim-attribute-group-index'
        />
    );
};

export {AttributeGroupsIndex};
