import 'jest-fetch-mock';
import React from 'react';
import { render } from '../../../../../../test-utils';
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

    const {
      findByText,
      findByTestId,
    } = render(
      <SimpleMultiOptionsAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      { all: true }
    );

    expect(await findByText('Name')).toBeInTheDocument();
    const inputOperator = await findByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.IN_LIST);

    expect(inputOperator).toBeInTheDocument();
    const inputValue = await findByTestId('edit-rules-input-1-value');
    expect(inputValue).toHaveValue(['test1', 'test3']);
  });
});
