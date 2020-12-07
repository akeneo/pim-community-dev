import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, render, screen} from '@testing-library/react';
import {ThemeProvider} from "styled-components";
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {DeleteModal} from 'pimui/js/attribute/form/delete/DeleteModal';

jest.mock('pimui/js/remover/attribute', () => ({}));

test('it render a confirm modal delete', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <DeleteModal
          isOpen={true}
          onClose={jest.fn()}
          onConfirm={jest.fn()}
        />
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
});
