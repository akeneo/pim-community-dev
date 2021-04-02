import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  fireEvent,
  renderWithProviders,
  screen,
} from '../../../../../../test-utils';
import {SetActionLine} from '../../../../../../src/pages/EditRules/components/actions/SetActionLine';
import {
  attributeSelect2Response,
  createAttribute,
  locales,
  scopes,
  uiLocales,
} from '../../../../factories';
import {clearAttributeRepositoryCache} from '../../../../../../src/repositories/AttributeRepository';
import userEvent from '@testing-library/user-event';

describe('SetActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the set action line with an unknown attribute', async () => {
    const defaultValues = {
      content: {
        actions: [
          {
            type: 'set',
            field: 'name',
            locale: 'en_US',
            scope: 'mobile',
            value: 'This is the name',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.actions[0].type', type: 'custom'},
      {name: 'content.actions[0].field', type: 'custom'},
      {name: 'content.actions[0].locale', type: 'custom'},
      {name: 'content.actions[0].scope', type: 'custom'},
      {name: 'content.actions[0].value', type: 'custom'},
    ];

    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(JSON.stringify(null));
      }
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get_available_fields'
        )
      ) {
        return Promise.resolve(JSON.stringify(attributeSelect2Response));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <SetActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.set_attribute.target_subtitle'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'pimee_catalog_rule.form.edit.actions.set_attribute.value_subtitle'
      )
    ).toBeInTheDocument();
    expect(
      screen.getAllByText(
        'pimee_catalog_rule.exceptions.unknown_attribute pimee_catalog_rule.exceptions.select_another_attribute pimee_catalog_rule.exceptions.or'
      )
    ).toHaveLength(1);
    expect(
      screen.getAllByText('pimee_catalog_rule.exceptions.create_attribute_link')
    ).toHaveLength(1);

    expect(screen.getByTestId('edit-rules-action-0-field')).toHaveValue('name');
    const inputValue = screen.getByTestId('edit-rules-action-0-value');
    expect(inputValue).toHaveValue('This is the name');
    expect(inputValue).toHaveProperty('disabled', true);
  });

  it('should be able display and change the attribute', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pim_enrich_attribute_rest_get?%7B%22identifier%22:%22name%22%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              code: 'name',
              type: 'pim_catalog_text',
              localizable: false,
              scopable: false,
            })
          )
        );
      }
      if (
        request.url.includes(
          'pim_enrich_attribute_rest_get?%7B%22identifier%22:%22brand%22%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              type: 'pim_catalog_simpleselect',
              localizable: true,
              scopable: true,
              labels: {en_US: 'The brand', fr_FR: 'La marque'},
              code: 'brand',
            })
          )
        );
      }
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get_available_fields'
        )
      ) {
        return Promise.resolve(JSON.stringify(attributeSelect2Response));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });
    const defaultValues = {
      content: {
        actions: [
          {
            type: 'set',
            field: 'name',
            locale: 'en_US',
            scope: 'mobile',
            value: 'This is the name',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.actions[0].type', type: 'custom'},
      {name: 'content.actions[0].field', type: 'custom'},
      {name: 'content.actions[0].locale', type: 'custom'},
      {name: 'content.actions[0].scope', type: 'custom'},
      {name: 'content.actions[0].value', type: 'custom'},
    ];

    renderWithProviders(
      <SetActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(await screen.findByTestId('edit-rules-action-0-value')).toHaveValue(
      'This is the name'
    );
    expect(
      screen.getByText(/name pim_common.required_label/i)
    ).toBeInTheDocument();

    await act(async () => {
      userEvent.click(await screen.findByTestId('edit-rules-action-0-field'));
      expect(
        (await screen.findByTestId('edit-rules-action-0-field')).children.length
      ).toBeGreaterThan(1);
      fireEvent.change(await screen.findByTestId('edit-rules-action-0-field'), {
        target: {value: 'brand'},
      });
    });
    expect(await screen.findByTestId('edit-rules-action-0-value')).toHaveValue(
      ''
    );
  });
});
