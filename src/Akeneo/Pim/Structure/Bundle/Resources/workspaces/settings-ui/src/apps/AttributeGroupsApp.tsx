import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {AttributeGroupsIndex} from '../pages';

const AttributeGroupsApp: FC = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <AttributeGroupsIndex />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {AttributeGroupsApp};
