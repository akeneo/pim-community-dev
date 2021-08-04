import {getTranslatedTableConfigurationFromVariationTemplate} from '../../../src/models/TranslatedTableConfigurationProvider';
import 'jest-fetch-mock';
import {ColumnCode, SelectOption, SelectOptionCode, TableConfiguration} from '../../../src/models/TableConfiguration';
import {LabelCollection} from '@akeneo-pim-community/shared';

const getOptionLabels: (
  result: TableConfiguration,
  columnCode: ColumnCode,
  optionCode: SelectOptionCode
) => LabelCollection = (result, columnCode, optionCode) => {
  const column = result.find(columnDefinition => columnDefinition.code === columnCode);
  if (!column) {
    throw new Error(`No column ${columnCode} found`);
  }
  if (typeof column['options'] === 'undefined') {
    throw new Error(`No options found for ${columnCode} column`);
  }
  const option = column['options'].find((option: SelectOption) => option.code === optionCode);
  if (!option) {
    throw new Error(`No option ${optionCode} found for ${columnCode} column`);
  }
  return option.labels;
};

describe('TranslatedTableConfigurationProvider', () => {
  it('should return translated table configuration', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('js/translation/en_US.js')) {
        return Promise.resolve(
          JSON.stringify({
            messages: {
              'jsmessages:table_attribute_template.nutrition-en.type.label': 'Type',
              'jsmessages:table_attribute_template.nutrition-en.quantity.label': 'Quantity',
              'jsmessages:table_attribute_template.nutrition-en.type.options.calories': 'Calories',
              'jsmessages:table_attribute_template.nutrition-en.type.options.fat': 'Fat',
            },
          })
        );
      }
      if (request.url.includes('js/translation/fr_FR.js')) {
        return Promise.resolve(
          JSON.stringify({
            messages: {
              'jsmessages:table_attribute_template.nutrition-en.quantity.label': 'Quantité',
              'jsmessages:table_attribute_template.nutrition-en.type.options.fat': 'Gros',
            },
          })
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const result = await getTranslatedTableConfigurationFromVariationTemplate('nutrition-en', ['en_US', 'fr_FR']);

    expect(result[0].labels).toEqual({en_US: 'Type'});
    expect(result[1].labels).toEqual({en_US: 'Quantity', fr_FR: 'Quantité'});
    expect(getOptionLabels(result, 'type', 'calories')).toEqual({en_US: 'Calories'});
    expect(getOptionLabels(result, 'type', 'fat')).toEqual({en_US: 'Fat', fr_FR: 'Gros'});
    expect(getOptionLabels(result, 'type', 'cholesterol')).toEqual({});
  });

  it('should return nothing if template variation is unknown', async () => {
    const result = await getTranslatedTableConfigurationFromVariationTemplate('unknown', ['en_US', 'fr_FR']);
    expect(result).toEqual([]);
  });
});
