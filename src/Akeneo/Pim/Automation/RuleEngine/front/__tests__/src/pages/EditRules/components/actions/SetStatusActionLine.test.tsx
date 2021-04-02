import React from 'react';
import 'jest-fetch-mock';
import {renderWithProviders, screen} from '../../../../../../test-utils';
import {locales, scopes, uiLocales} from '../../../../factories';
import {SetStatusActionLine} from '../../../../../../src/pages/EditRules/components/actions/SetStatusActionLine';

describe('SetStatusActionLine', () => {
  it('should be able to display a new set status action', async () => {
    renderWithProviders(
      <SetStatusActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        handleDelete={jest.fn()}
      />,
      {all: true}
    );
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.set_status.title'
      )
    ).toBeInTheDocument();

    const select = screen.getByTestId('edit-rules-actions-0-value');
    expect(select).toHaveValue('');
    expect(select.children).toHaveLength(3);
    expect(select.children.item(0)?.getAttribute('value')).toEqual('');
    expect(select.children.item(1)?.getAttribute('value')).toEqual('enabled');
    expect(select.children.item(2)?.getAttribute('value')).toEqual('disabled');
  });

  it('should be able to display an existing set status action', async () => {
    const defaultValues = {
      content: {
        actions: [
          {
            type: 'set',
            field: 'enabled',
            value: true,
          },
        ],
      },
    };
    const toRegister = [
      {name: 'content.actions[0].field', type: 'custom'},
      {name: 'content.actions[0].value', type: 'custom'},
      {name: 'content.actions[0].type', type: 'custom'},
    ];

    renderWithProviders(
      <SetStatusActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        handleDelete={jest.fn()}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.set_status.title'
      )
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-actions-0-value')).toHaveValue(
      'enabled'
    );
  });
});
