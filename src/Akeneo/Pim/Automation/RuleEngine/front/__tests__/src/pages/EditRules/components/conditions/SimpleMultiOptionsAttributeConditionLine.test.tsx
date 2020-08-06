import 'jest-fetch-mock';
import React from 'react';
import { render, screen } from '../../../../../../test-utils';
import { SimpleMultiOptionsAttributeCondition } from '../../../../../../src/models/conditions';
import { Operator } from '../../../../../../src/models/Operator';
import { SimpleMultiOptionsAttributeConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/SimpleMultiOptionsAttributeConditionLine';
import {
  attributeOptionsSelect2Response,
  createAttribute,
  locales,
  scopes,
} from '../../../../factories';
import { clearAttributeRepositoryCache } from '../../../../../../src/repositories/AttributeRepository';

jest.mock('../../../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../src/dependenciesTools/components/AssetManager/AssetSelector'
);
jest.mock(
  '../../../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);

describe('SimpleMultiOptionsAttributeConditionLine', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    clearAttributeRepositoryCache();
  });
  it('should display the simple multi options line with existing options', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              localizable: true,
              scopable: true,
              type: 'pim_catalog_simpleselect',
            })
          )
        );
      }

      if (request.url.includes('pim_ui_ajaxentity_list')) {
        return Promise.resolve(JSON.stringify(attributeOptionsSelect2Response));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: SimpleMultiOptionsAttributeCondition = {
      field: 'name',
      operator: Operator.IN_LIST,
      value: ['test1', 'test3'],
    };

    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'name',
            operator: Operator.IN_LIST,
            value: ['test1', 'test3'],
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[1].value', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
    ];

    render(
      <SimpleMultiOptionsAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(await screen.findByText('Name')).toBeInTheDocument();
    const inputOperator = screen.getByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.IN_LIST);

    expect(inputOperator).toBeInTheDocument();
    const inputValue = screen.getByTestId('edit-rules-input-1-value');
    expect(inputValue).toHaveValue(['test1', 'test3']);
  });
});
