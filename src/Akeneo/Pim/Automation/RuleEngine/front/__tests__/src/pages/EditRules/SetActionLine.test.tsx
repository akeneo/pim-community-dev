import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders } from '../../../../test-utils';
import { SetActionLine } from '../../../../src/pages/EditRules/components/actions/SetActionLine';
import { SetAction } from '../../../../src/models/actions';
import { attributeSelect2Response, locales, scopes } from '../../factories';

const actionWithLocalizableScopableAttribute: SetAction = {
  type: 'set',
  field: 'description',
  locale: 'en_US',
  scope: 'mobile',
  value: 'This is the description',
};

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');

describe('SetActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
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
        action={actionWithLocalizableScopableAttribute}
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
      await findByText(
        'pimee_catalog_rule.form.edit.actions.set_attribute.unknown_attribute'
      )
    ).toBeInTheDocument();
  });
});
