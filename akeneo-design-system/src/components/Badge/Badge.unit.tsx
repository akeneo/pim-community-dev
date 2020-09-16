import React from 'react';
import {ThemeProvider} from 'styled-components';
import {Badge} from './Badge';
import {pimTheme} from 'theme/pim';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';

test('it renders its children properly', () => {
  const {getByText} = render(
    <ThemeProvider theme={pimTheme}>
      <Badge>Badge content</Badge>
    </ThemeProvider>
  );

  expect(getByText('Badge content')).toBeInTheDocument();
});
