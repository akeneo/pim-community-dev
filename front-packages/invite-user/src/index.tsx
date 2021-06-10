import React from 'react';
import ReactDOM from 'react-dom';
import styled, {ThemeProvider} from 'styled-components';
import {CommonStyle, pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {InviteUserApp} from './feature';

const Container = styled.div`
  ${CommonStyle}
`;

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      {/* @ts-ignore */}
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations}>
        <Container>
          <InviteUserApp />
        </Container>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);
