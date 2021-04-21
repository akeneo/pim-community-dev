import React from 'react';
import {fireEvent, render} from '@testing-library/react';
import {SearchBar} from '@akeneo-pim-community/shared';
import {DependenciesContext} from '../DependenciesContext';
import {mockedDependencies} from '../../tests/mockedDependencies';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';

test('It calls the onSearchChange callback when the value is changed', () => {
  const onSearchChange = jest.fn();

  console.log('ffff');
  const {getByTitle} = render(
    <DependenciesContext.Provider value={mockedDependencies}>
      <ThemeProvider theme={pimTheme}>
        <SearchBar onSearchChange={onSearchChange} searchValue="hey" count={12} />
      </ThemeProvider>
    </DependenciesContext.Provider>
  );

  fireEvent.change(getByTitle('pim_common.search'), {target: {value: 'hey!'}});

  expect(onSearchChange).toBeCalledWith('hey!');
});
