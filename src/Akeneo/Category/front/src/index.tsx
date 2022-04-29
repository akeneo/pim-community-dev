import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {CategoriesApp} from "./feature/CategoriesApp";

ReactDOM.render(
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
        <CategoriesApp setCanLeavePage={() => true}/>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
,  document.getElementById('root')
);
