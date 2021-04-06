import React, {FC} from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CategoriesIndex} from '../pages';

const CategoriesApp: FC = () => {
  return (
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <CategoriesIndex />
      </ThemeProvider>
    </DependenciesProvider>
  );
};

export {CategoriesApp};
