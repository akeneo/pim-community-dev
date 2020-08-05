import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders, screen } from '../../../../../../test-utils';
import { locales, scopes } from '../../../../factories';
import { SetStatusActionLine } from '../../../../../../src/pages/EditRules/components/actions/SetStatusActionLine';
import {
  createSetStatusAction,
  SetStatusAction,
} from '../../../../../../src/models/actions';

jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../../../src/fetch/categoryTree.fetcher.ts');
jest.mock('../../../../../../src/dependenciesTools/components/AssetManager/AssetSelector');
jest.mock(
  '../../../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);

describe('SetStatusActionLine', () => {
  it('should be able to display a new set status action', async () => {
    renderWithProviders(
      <SetStatusActionLine
        action={createSetStatusAction()}
        lineNumber={0}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        handleDelete={jest.fn()}
      />,
      { all: true }
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
    const action: SetStatusAction = {
      type: 'set',
      field: 'enabled',
      value: true,
    };
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
      { name: 'content.actions[0].field', type: 'custom' },
      { name: 'content.actions[0].value', type: 'custom' },
      { name: 'content.actions[0].type', type: 'custom' },
    ];

    renderWithProviders(
      <SetStatusActionLine
        action={action}
        lineNumber={0}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        handleDelete={jest.fn()}
      />,
      { all: true },
      { defaultValues, toRegister }
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
