import React from 'react';
import {Sidebar} from './Sidebar';
import {render, screen} from '@testing-library/react';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

test('it renders a sidebar', () => {
  const onTabChange = jest.fn();

  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <Sidebar tabs={[{code: 'my_tab', label: 'My tab'}]} currentTab="my_tab" onTabChange={onTabChange} />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('My tab')).toBeInTheDocument();
  // const input = screen.getByLabelText('My label') as HTMLInputElement;
  // fireEvent.change(input, {target: {value: 'Cool'}});
  // expect(handleChange).toHaveBeenCalledWith('Cool');
});
