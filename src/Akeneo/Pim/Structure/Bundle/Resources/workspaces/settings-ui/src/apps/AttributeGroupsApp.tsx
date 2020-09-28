import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {AttributeGroupsIndex} from '../pages';
import {AttributeGroupsDataGridProvider} from '../components';

const AttributeGroupsApp: FC = () => {
  return (
    <DependenciesProvider>
      <AkeneoThemeProvider>
        <div>
          <AttributeGroupsDataGridProvider>
            <AttributeGroupsIndex />
          </AttributeGroupsDataGridProvider>
        </div>
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
};

export {AttributeGroupsApp};
