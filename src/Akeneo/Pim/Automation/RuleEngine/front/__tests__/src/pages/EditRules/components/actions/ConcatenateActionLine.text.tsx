import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  renderWithProviders,
  screen,
  fireEvent,
} from '../../../../../../test-utils';
import userEvent from '@testing-library/user-event';
import {clearAttributeRepositoryCache} from '../../../../../../src/repositories/AttributeRepository';
import {
  attributeSelect2Response,
  createAttribute,
  currencies,
  locales,
  scopes,
  uiLocales,
} from '../../../../factories';
import {ConcatenateActionLine} from '../../../../../../src/pages/EditRules/components/actions/ConcatenateActionLine';
import {AttributeType} from '../../../../../../src/models';

const defaultValues = {
  content: {
    actions: [
      {
        type: 'concatenate',
        to: {
          field: 'description',
          locale: 'en_US',
          scope: 'mobile',
        },
        from: [
          {
            field: 'name',
          },
          {
            new_line: null,
          },
          {
            text: ' this is a text',
          },
        ],
      },
      {
        type: 'concatenate',
        to: {
          field: 'name',
        },
        from: [
          {
            field: 'name',
          },
          {
            text: ' this is a text',
          },
        ],
      },
    ],
  },
};

const toRegister = [
  {name: '`content.actions[0].to.field`', type: 'custom'},
  {name: '`content.actions[0].to.locale`', type: 'custom'},
  {name: '`content.actions[0].to.scope`', type: 'custom'},
];

const descriptionAttribute = createAttribute({
  code: 'description',
  type: AttributeType.TEXTAREA,
  labels: {
    en_US: 'DescriptionUS',
    fr_FR: 'Description',
  },
  localizable: true,
  scopable: true,
});

const nameAttribute = createAttribute({
  code: 'name',
  type: AttributeType.TEXT,
  labels: {
    en_US: 'Name',
    fr_FR: 'Nom',
  },
  localizable: false,
  scopable: false,
});

const brandAttribute = createAttribute({
  code: 'brand',
  type: AttributeType.OPTION_SIMPLE_SELECT,
  labels: {
    en_US: 'BrandUS',
    fr_FR: 'Marque',
  },
  localizable: false,
  scopable: false,
});

const response = (request: Request) => {
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('description')
  ) {
    return Promise.resolve(JSON.stringify(descriptionAttribute));
  }
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('name')
  ) {
    return Promise.resolve(JSON.stringify(nameAttribute));
  }
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('brand')
  ) {
    return Promise.resolve(JSON.stringify(brandAttribute));
  }
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('unknown_attribute')
  ) {
    return Promise.resolve(JSON.stringify(null));
  }
  if (request.url.includes('pim_enrich_currency_rest_index')) {
    return Promise.resolve(JSON.stringify(currencies));
  }
  if (
    request.url.includes('pimee_enrich_rule_definition_get_available_fields')
  ) {
    return Promise.resolve(JSON.stringify(attributeSelect2Response));
  }
  throw new Error(`The "${request.url}" url is not mocked.`);
};

describe('ConcatenateActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the concatenate action line', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <ConcatenateActionLine
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
        currentCatalogLocale={'en_US'}
        handleDelete={() => {}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.select_target'
      )
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-action-0-to-field')).toHaveValue(
      'description'
    );
    expect(screen.getByTestId('edit-rules-action-0-to-scope')).toHaveValue(
      'mobile'
    );
    expect(screen.getByTestId('edit-rules-action-0-to-locale')).toHaveValue(
      'en_US'
    );

    expect(await screen.findByText('this is a text')).toBeInTheDocument();
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.line_break'
      )
    ).toBeInTheDocument();
  });

  it('should be able to add a field in the source list', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <ConcatenateActionLine
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
        currentCatalogLocale={'en_US'}
        handleDelete={() => {}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.select_target'
      )
    ).toBeInTheDocument();

    const addFieldButton = await screen.findByTestId(
      'edit-rules-action-0-add-attribute'
    );
    expect(addFieldButton).toBeInTheDocument();
    expect(screen.queryAllByText('BrandUS').length).toBe(0);
    await act(async () => {
      userEvent.click(addFieldButton);
      expect(
        (await screen.findByTestId('edit-rules-action-0-add-attribute'))
          .children.length
      ).toBeGreaterThan(1);
      fireEvent.change(
        await screen.findByTestId('edit-rules-action-0-add-attribute'),
        {
          target: {value: 'brand'},
        }
      );
    });
    expect(screen.queryAllByText('BrandUS').length).toBeGreaterThan(0);
  });

  it('should be able to add a text in the source list', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <ConcatenateActionLine
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
        currentCatalogLocale={'en_US'}
        handleDelete={() => {}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.select_target'
      )
    ).toBeInTheDocument();

    const addTextButton = await screen.findByTestId(
      'edit-rules-action-0-add-text'
    );
    expect(addTextButton).toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-action-operation-list-3-text')
    ).not.toBeInTheDocument();
    act(() => {
      userEvent.click(addTextButton);
    });
    expect(
      await screen.findByTestId('edit-rules-action-operation-list-3-text')
    ).toBeInTheDocument();
  });

  it('should be able to add a line break in the source list if target is textarea', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <ConcatenateActionLine
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
        currentCatalogLocale={'en_US'}
        handleDelete={() => {}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.select_target'
      )
    ).toBeInTheDocument();

    const addLineBreakdButton = await screen.findByTestId(
      'edit-rules-action-0-add-new-line'
    );
    expect(addLineBreakdButton).toBeInTheDocument();
    expect(
      screen.queryAllByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.line_break'
      ).length
    ).toBe(1);
    act(() => {
      userEvent.click(addLineBreakdButton);
    });
    expect(
      screen.queryAllByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.line_break'
      ).length
    ).toBe(2);
  });

  it('should not be able to add a line break in the source list if target is text', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <ConcatenateActionLine
        lineNumber={1}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
        currentCatalogLocale={'en_US'}
        handleDelete={() => {}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.concatenate.select_target'
      )
    ).toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-action-1-add-new-line')
    ).not.toBeInTheDocument();
  });
});
