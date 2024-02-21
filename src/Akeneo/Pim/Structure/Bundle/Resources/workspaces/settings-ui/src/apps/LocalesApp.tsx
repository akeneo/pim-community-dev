import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {LocalesIndex} from '../pages';
import {LocalesIndexProvider} from '../components/providers';

const LocalesApp: FC = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <LocalesIndexProvider>
          <LocalesIndex />
        </LocalesIndexProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {LocalesApp};
