import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {LocalesEEIndex} from '../pages';
import {LocalesIndexProvider} from '@akeneo-pim-community/settings-ui';

const LocalesEEApp: FC = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <LocalesIndexProvider>
          <LocalesEEIndex />
        </LocalesIndexProvider>
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {LocalesEEApp};
