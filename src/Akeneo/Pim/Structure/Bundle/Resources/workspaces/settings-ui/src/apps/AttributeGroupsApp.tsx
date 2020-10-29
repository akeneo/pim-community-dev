import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AttributeGroupsIndex} from '../pages';
import {AttributeGroupsIndexProvider} from '../components';

const AttributeGroupsApp: FC = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <div>
          <AttributeGroupsIndexProvider>
            <AttributeGroupsIndex />
          </AttributeGroupsIndexProvider>
        </div>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {AttributeGroupsApp};
