import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {AkeneoThemeProvider} from '@akeneo-pim-community/shared';
import {AttributeGroupsIndex} from '../pages';
import {AttributeGroupsIndexProvider} from '../components';

const AttributeGroupsApp: FC = () => {
  return (
    <DependenciesProvider>
      <AkeneoThemeProvider>
        <div>
          <AttributeGroupsIndexProvider>
            <AttributeGroupsIndex />
          </AttributeGroupsIndexProvider>
        </div>
      </AkeneoThemeProvider>
    </DependenciesProvider>
  );
};

export {AttributeGroupsApp};
