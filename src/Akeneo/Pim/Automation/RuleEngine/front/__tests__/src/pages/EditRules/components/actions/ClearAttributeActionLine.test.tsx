import {
  renderWithProviders,
  act,
  fireEvent,
  screen,
} from '../../../../../../test-utils';
import React from 'react';
import 'jest-fetch-mock';
import {ClearAttributeActionLine} from '../../../../../../src/pages/EditRules/components/actions/ClearAttributeActionLine';
import {clearAttributeRepositoryCache} from '../../../../../../src/repositories/AttributeRepository';
import userEvent from '@testing-library/user-event';
import {
  attributeSelect2Response,
  createAttribute,
  locales,
  scopes,
  uiLocales,
} from '../../../../factories';

describe('ClearAttributeActionLine', () => {
  beforeEach(() => {
    clearAttributeRepositoryCache();
    fetchMock.resetMocks();
  });

  it('should display the clear attribute action line without locale or scope', async () => {
    fetchMock.mockResponse((request: Request) => {
      // attribute values
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(
          JSON.stringify(createAttribute({scopable: false, localizable: false}))
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const defaultValues = {
      content: {
        actions: [
          {},
          {
            type: 'clear',
            field: 'name',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.actions[1].type', type: 'custom'},
      {name: 'content.actions[1].field', type: 'custom'},
    ];

    renderWithProviders(
      <ClearAttributeActionLine
        currentCatalogLocale={'en_US'}
        lineNumber={1}
        handleDelete={jest.fn()}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      screen.getAllByText('pimee_catalog_rule.form.helper.clear_attribute')
    ).toHaveLength(2);
    expect(screen.getByTestId('edit-rules-action-1-field')).toHaveValue('name');
    expect(
      screen.queryByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();
    expect(
      screen.queryByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();
  });

  it('should display the clear attribute action line with locale and scope', async () => {
    fetchMock.mockResponse((request: Request) => {
      // attribute values
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(JSON.stringify(createAttribute({})));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const defaultValues = {
      content: {
        actions: [
          {},
          {
            type: 'clear',
            field: 'name',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.actions[1].type', type: 'custom'},
      {name: 'content.actions[1].field', type: 'custom'},
    ];

    renderWithProviders(
      <ClearAttributeActionLine
        currentCatalogLocale={'en-US'}
        lineNumber={1}
        handleDelete={jest.fn()}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      screen.getAllByText('pimee_catalog_rule.form.helper.clear_attribute')
    ).toHaveLength(2);
    expect(screen.getByTestId('edit-rules-action-1-field')).toHaveValue('name');
    expect(
      screen.getByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
  });

  it('should remove/add the scope and label when switching from a scopable attribute to a non-scopable one', async () => {
    fetchMock.mockResponse((request: Request) => {
      // attribute values
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        if (request.url.includes('name')) {
          return Promise.resolve(
            JSON.stringify(
              createAttribute({
                code: 'name',
                scopable: true,
                localizable: true,
              })
            )
          );
        }

        if (request.url.includes('description')) {
          return Promise.resolve(
            JSON.stringify(
              createAttribute({
                code: 'description',
                scopable: false,
                localizable: false,
              })
            )
          );
        }
      }
      // attributes available for the rule
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
          {},
          {
            type: 'clear',
            field: 'name',
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.actions[1].type', type: 'custom'},
      {name: 'content.actions[1].field', type: 'custom'},
    ];

    renderWithProviders(
      <ClearAttributeActionLine
        currentCatalogLocale={'en-US'}
        lineNumber={1}
        handleDelete={jest.fn()}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    const attributeSelector = await screen.findByTestId(
      'edit-rules-action-1-field'
    );
    expect(attributeSelector).toBeInTheDocument();
    expect(attributeSelector).toHaveValue('name');
    expect(
      screen.getByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();

    await act(async () => {
      userEvent.click(await screen.findByTestId('edit-rules-action-1-field'));
      expect(
        screen.getByTestId('edit-rules-action-1-field').children.length
      ).toBeGreaterThan(1);
      fireEvent.change(await screen.findByTestId('edit-rules-action-1-field'), {
        target: {value: 'description'},
      });
    });

    expect(
      screen.queryByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();
    expect(
      screen.queryByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();

    await act(async () => {
      userEvent.click(await screen.findByTestId('edit-rules-action-1-field'));
      expect(
        screen.getByTestId('edit-rules-action-1-field').children.length
      ).toBeGreaterThan(1);
      fireEvent.change(await screen.findByTestId('edit-rules-action-1-field'), {
        target: {value: 'name'},
      });
    });

    expect(
      screen.getByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
  });
});
