import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AttributeGroupsIndex} from '../pages';

const AttributeGroupsApp: FC = () => {
    return (
        <DependenciesProvider>
            <div>
                <AttributeGroupsIndex />
            </div>
        </DependenciesProvider>
    );
};

export {AttributeGroupsApp};
