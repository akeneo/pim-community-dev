import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders } from '../../../../test-utils';
import { SetActionLine } from '../../../../src/pages/EditRules/components/actions/SetActionLine';
import { SetAction } from '../../../../src/models/actions';
import {
  attributeSelect2Response,
  createAttribute,
  locales,
  scopes,
} from '../../factories';
import { clearAttributeRepositoryCache } from '../../../../src/repositories/AttributeRepository';

const createSetAction = (data?: { [key: string]: any }): SetAction => {
  return {
    type: 'set',
    field: 'name',
    locale: 'en_US',
    scope: 'mobile',
    value: 'This is the name',
    ...data,
  };
};

jest.mock('../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../src/fetch/categoryTree.fetcher.ts');

describe('SetActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the set action line with an unknown attribute', async () => {
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

    const { findByText } = renderWithProviders(
      <SetActionLine
        action={createSetAction()}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={() => {}}
      />,
      { all: true }
    );

    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.actions.set_attribute.target_subtitle'
      )
    ).toBeInTheDocument();
    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      await findByText(
        'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      await findByText(
        'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    expect(
      await findByText(
        'pimee_catalog_rule.form.edit.actions.set_attribute.value_subtitle'
      )
    ).toBeInTheDocument();
    expect(
      await findByText('pimee_catalog_rule.form.edit.unknown_attribute')
    ).toBeInTheDocument();
  });

  it('should display the set action line with a text attribute', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              type: 'pim_catalog_text',
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

    const { findByText, findByTestId } = renderWithProviders(
      <SetActionLine
        action={createSetAction()}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={() => {}}
      />,
      { all: true }
    );

    const valueInput = await findByTestId('edit-rules-action-1-value');
    expect(valueInput).toBeInTheDocument();
    expect(valueInput).toHaveValue('This is the name');
    expect(
      await findByText('Name pim_common.required_label')
    ).toBeInTheDocument();
  });
});
