import React from 'react';
import {render, screen} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Dummy} from './Dummy';

jest.unmock('./Dummy');

test('it renders section without error', () => {
  render(
    <ThemeProvider theme={pimTheme}>
      <Dummy label="foo"/>
    </ThemeProvider>
  );

  expect(screen.getByText('foo')).toBeInTheDocument();
});
