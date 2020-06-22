import {
  renderWithProviders,
  act,
  fireEvent,
} from '../../../../../../test-utils';
import React from 'react';
import 'jest-fetch-mock';
import { ClearAttributeActionLine } from '../../../../../../src/pages/EditRules/components/actions/ClearAttributeActionLine';
import { clearAttributeRepositoryCache } from '../../../../../../src/repositories/AttributeRepository';
import userEvent from '@testing-library/user-event';
import {
  attributeSelect2Response,
  createAttribute,
  locales,
  scopes,
} from '../../../../factories';
import { ClearAttributeAction } from '../../../../../../src/models/actions';

jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../../../src/fetch/categoryTree.fetcher.ts');

const action: ClearAttributeAction = {
  type: 'clear',
  field: 'name',
};

describe('ClearAttributeActionLine', () => {
  beforeEach(() => {
    clearAttributeRepositoryCache();
    fetchMock.resetMocks();
  });

  it('should display the clear attribute action line without locale or scope', async () => {
    fetchMock.mockResponses([
      JSON.stringify(
        createAttribute({
          scopable: false,
          localizable: false,
        })
      ),
      { status: 200 },
    ]);

    const {
      findByText,
      queryByText,
      findByTestId,
    } = renderWithProviders(
      <ClearAttributeActionLine
        currentCatalogLocale={'en_US'}
        lineNumber={1}
        action={action}
        handleDelete={() => {}}
        locales={locales}
        scopes={scopes}
      />,
      { all: true }
    );

    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(await findByTestId('edit-rules-action-1-field')).toHaveValue('name');
    expect(
      queryByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();
    expect(
      queryByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();
  });

  it('should display the clear attribute action line with locale and scope', async () => {
    fetchMock.mockResponse(() => {
      return Promise.resolve(JSON.stringify(createAttribute({})));
    });

    const {
      findByText,
      findByTestId,
      queryByText,
    } = renderWithProviders(
      <ClearAttributeActionLine
        currentCatalogLocale={'en-US'}
        lineNumber={1}
        action={action}
        handleDelete={() => {}}
        locales={locales}
        scopes={scopes}
      />,
      { all: true }
    );

    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(await findByTestId('edit-rules-action-1-field')).toHaveValue('name');
    expect(
      queryByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      queryByText(
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

    const {
      findByText,
      findByTestId,
      queryByText,
    } = renderWithProviders(
      <ClearAttributeActionLine
        currentCatalogLocale={'en-US'}
        lineNumber={1}
        action={action}
        handleDelete={() => {}}
        locales={locales}
        scopes={scopes}
      />,
      { all: true }
    );

    const attributeSelector = await findByTestId('edit-rules-action-1-field');
    expect(attributeSelector).toBeInTheDocument();
    expect(attributeSelector).toHaveValue('name');
    expect(
      await findByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      await findByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();

    await act(async () => {
      userEvent.click(await findByTestId('edit-rules-action-1-field'));
      expect(
        (await findByTestId('edit-rules-action-1-field')).children.length
      ).toBeGreaterThan(1);
      fireEvent.change(await findByTestId('edit-rules-action-1-field'), {
        target: { value: 'description' },
      });
    });

    expect(
      queryByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();
    expect(
      queryByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).not.toBeInTheDocument();

    await act(async () => {
      userEvent.click(await findByTestId('edit-rules-action-1-field'));
      expect(
        (await findByTestId('edit-rules-action-1-field')).children.length
      ).toBeGreaterThan(1);
      fireEvent.change(await findByTestId('edit-rules-action-1-field'), {
        target: { value: 'name' },
      });
    });

    expect(
      queryByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      queryByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
  });
});
