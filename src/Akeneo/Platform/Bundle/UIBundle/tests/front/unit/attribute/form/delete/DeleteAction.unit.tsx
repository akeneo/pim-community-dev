import '@testing-library/jest-dom';
import React from 'react';
import {fireEvent, render, screen, getByText} from '@testing-library/react';
import {ThemeProvider} from "styled-components";
import {pimTheme} from 'akeneo-design-system';
import {DependenciesContext, DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {dependencies} from '@akeneo-pim-community/legacy-bridge/src/provider/dependencies';
import {DeleteAction} from 'pimui/js/attribute/form/delete/DeleteAction';

jest.mock('@akeneo-pim-community/legacy-bridge/src/provider/dependencies');

const xhrSuccess = jest.fn().mockImplementation(() => {return xhr;});
const xhrFail = jest.fn().mockImplementation(() => {return xhr;});
const xhr: any = {
  done: xhrSuccess,
  fail: xhrFail,
};

jest.mock('pimui/js/remover/attribute', () => ({
  remove: jest.fn().mockImplementation(() => xhr),
}));

test('it render a delete action button', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <DeleteAction attributeCode={'foo'}/>
      </ThemeProvider>
    </DependenciesProvider>
  );

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
});

test('it open the confirm modal on click', () => {
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <DeleteAction attributeCode={'foo'}/>
      </ThemeProvider>
    </DependenciesProvider>
  );

  const openModalButton = screen.getByText('pim_common.delete');
  fireEvent.click(openModalButton);

  expect(screen.getByText('pim_common.confirm_deletion')).toBeInTheDocument();
});

test('it calls the attribute remover when confirm is clicked', () => {
  xhrSuccess.mockImplementationOnce(callback => {
    callback({});
    return xhr;
  });

  render(
    <DependenciesContext.Provider value={dependencies}>
      <ThemeProvider theme={pimTheme}>
        <DeleteAction attributeCode={'foo'}/>
      </ThemeProvider>
    </DependenciesContext.Provider>
  );

  const openModalButton = screen.getByText('pim_common.delete');
  fireEvent.click(openModalButton);

  const modal = screen.getByRole('dialog');

  const confirmDeleteButton = getByText(modal, 'pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  expect(dependencies.notify).toHaveBeenCalledWith('success', 'pim_enrich.entity.attribute.flash.delete.success');
  expect(dependencies.router.redirect).toHaveBeenCalledWith('pim_enrich_attribute_index');
});

test('it display an error when the delete failed', () => {
  xhrFail.mockImplementationOnce(callback => {
    callback({});
    return xhr;
  });

  render(
    <DependenciesContext.Provider value={dependencies}>
      <ThemeProvider theme={pimTheme}>
        <DeleteAction attributeCode={'foo'}/>
      </ThemeProvider>
    </DependenciesContext.Provider>
  );

  const openModalButton = screen.getByText('pim_common.delete');
  fireEvent.click(openModalButton);

  const modal = screen.getByRole('dialog');

  const confirmDeleteButton = getByText(modal, 'pim_common.delete');
  fireEvent.click(confirmDeleteButton);

  expect(dependencies.notify).toHaveBeenCalledWith('error', 'pim_enrich.entity.attribute.flash.delete.fail');
});
