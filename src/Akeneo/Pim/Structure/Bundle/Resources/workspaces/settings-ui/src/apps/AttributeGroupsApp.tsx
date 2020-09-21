import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {AttributeGroupsIndex} from '../pages';

const AttributeGroupsApp: FC = () => {
    return (
        <DependenciesProvider>
            <AkeneoThemeProvider>
                <div>
                    <AttributeGroupsIndex />
                </div>
            </AkeneoThemeProvider>
        </DependenciesProvider>
    );
};

export {AttributeGroupsApp};
