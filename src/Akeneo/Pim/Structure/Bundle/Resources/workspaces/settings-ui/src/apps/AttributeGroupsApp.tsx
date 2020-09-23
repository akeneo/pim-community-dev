import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {AttributeGroupsIndex} from '../pages';
import {AttributeGroupsListProvider} from "../components/shared/providers";

const AttributeGroupsApp: FC = () => {
    return (
        <DependenciesProvider>
            <AkeneoThemeProvider>
                <div>
                    <AttributeGroupsListProvider>
                        <AttributeGroupsIndex />
                    </AttributeGroupsListProvider>
                </div>
            </AkeneoThemeProvider>
        </DependenciesProvider>
    );
};

export {AttributeGroupsApp};
