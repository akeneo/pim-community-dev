import { renderWithProviders, act, fireEvent } from '../../../../test-utils';
import React from 'react';
import 'jest-fetch-mock';
import { ClearAttributeAction } from '../../../../src/models/actions';
import { ClearAttributeActionLine } from '../../../../src/pages/EditRules/components/actions/ClearAttributeActionLine';
import { Router } from '../../../../src/dependenciesTools';
import { clearCache } from '../../../../src/repositories/AttributeRepository';
import userEvent from '@testing-library/user-event';
import {
  attributeSelect2Response,
  createAttribute,
  locales,
  scopes,
} from '../../factories';

const router: Router = {
  generate: jest.fn(),
  redirect: jest.fn(),
};

const translate = jest.fn((key: string) => key);
const action: ClearAttributeAction = {
  module: ClearAttributeActionLine,
  type: 'clear',
  field: 'name',
};

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');

describe('ClearAttributeActionLine', () => {
  beforeEach(() => {
    clearCache();
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
        translate={translate}
        router={router}
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
      queryByText('Channel pim_common.required_label')
    ).not.toBeInTheDocument();
    expect(
      queryByText('Locale pim_common.required_label')
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
        translate={translate}
        router={router}
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
    expect(queryByText('Locale pim_common.required_label')).toBeInTheDocument();
    expect(
      queryByText('Channel pim_common.required_label')
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
        translate={translate}
        router={router}
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
      await findByText('Locale pim_common.required_label')
    ).toBeInTheDocument();
    expect(
      await findByText('Channel pim_common.required_label')
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
      queryByText('Channel pim_common.required_label')
    ).not.toBeInTheDocument();
    expect(
      queryByText('Locale pim_common.required_label')
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
      queryByText('Channel pim_common.required_label')
    ).toBeInTheDocument();
    expect(queryByText('Locale pim_common.required_label')).toBeInTheDocument();
  });
});
