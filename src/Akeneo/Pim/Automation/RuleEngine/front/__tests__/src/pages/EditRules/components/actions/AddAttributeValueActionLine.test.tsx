import React from 'react';
import 'jest-fetch-mock';
import { act, renderWithProviders, screen } from '../../../../../../test-utils';
import { createAttribute, locales, scopes } from '../../../../factories';
import { clearAttributeRepositoryCache } from '../../../../../../src/repositories/AttributeRepository';
import { AttributeType } from '../../../../../../src/models';
import { AddAttributeValueActionLine } from '../../../../../../src/pages/EditRules/components/actions/AddAttributeValueActionLine';
import { AddAttributeValueAction } from '../../../../../../src/models/actions';
import userEvent from '@testing-library/user-event';

jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../../../src/fetch/categoryTree.fetcher.ts');
jest.mock(
  '../../../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);
jest.mock(
  '../../../../../../src/dependenciesTools/components/AssetManager/AssetSelector'
);

describe('AddAttributeValueActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the add action line with existing values', async () => {
    const defaultValues = {
      content: {
        actions: [
          {
            type: 'add',
            field: 'collection',
            locale: 'en_US',
            scope: 'mobile',
            items: ['winter_2016'],
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.actions[0].type', type: 'custom' },
      { name: 'content.actions[0].field', type: 'custom' },
      { name: 'content.actions[0].locale', type: 'custom' },
      { name: 'content.actions[0].scope', type: 'custom' },
      { name: 'content.actions[0].items', type: 'custom' },
    ];

    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pim_enrich_attribute_rest_get?%7B%22identifier%22:%22collection%22%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              code: 'collection',
              type: AttributeType.OPTION_MULTI_SELECT,
            })
          )
        );
      } else if (request.url.includes('pim_ui_ajaxentity_list')) {
        return Promise.resolve(
          JSON.stringify({
            results: [
              { id: 'autumn_2016', text: 'Autumn 2016' },
              { id: 'winter_2016', text: 'Winter 2016' },
            ],
          })
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <AddAttributeValueActionLine
        action={defaultValues.content.actions[0] as AddAttributeValueAction}
        lineNumber={0}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.add_attribute_value.title'
      )
    ).toBeInTheDocument();

    expect(screen.getByTestId('edit-rules-action-0-field')).toHaveValue(
      'collection'
    );
    expect(screen.getByTestId('edit-rules-action-0-field')).toHaveProperty(
      'disabled',
      false
    );
    const inputValue = screen.getByTestId('edit-rules-action-0-items');
    expect(inputValue).toHaveValue(['winter_2016']);
    await act(async () => {
      userEvent.click(await screen.findByTestId('edit-rules-action-0-items'));
      expect(
        (await screen.findByTestId('edit-rules-action-0-items')).children.length
      ).toBeGreaterThan(1);
    });
  });
});
