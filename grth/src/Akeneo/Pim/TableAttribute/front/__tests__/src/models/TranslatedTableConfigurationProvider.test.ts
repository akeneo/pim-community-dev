import {
  ColumnCode,
  getTranslatedTableConfigurationFromVariationTemplate,
  SelectOption,
  SelectOptionCode,
  TableConfiguration,
} from '../../../src';
import 'jest-fetch-mock';
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
              'jsmessages:table_attribute_template.nutrition-unitedkingdom.nutrition.label': 'Nutrition',
              'jsmessages:table_attribute_template.nutrition-unitedkingdom.per_100g.label': 'Per 100g',
              'jsmessages:table_attribute_template.nutrition-unitedkingdom.nutrition.options.carbohydrate':
                'Carbohydrate',
              'jsmessages:table_attribute_template.nutrition-unitedkingdom.nutrition.options.fat': 'Fat',
            },
          })
        );
      }
      if (request.url.includes('js/translation/fr_FR.js')) {
        return Promise.resolve(
          JSON.stringify({
            messages: {
              'jsmessages:table_attribute_template.nutrition-unitedkingdom.per_100g.label': 'Pour 100g',
              'jsmessages:table_attribute_template.nutrition-unitedkingdom.nutrition.options.fat': 'Gros',
            },
          })
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const result = await getTranslatedTableConfigurationFromVariationTemplate('nutrition-unitedkingdom', [
      'en_US',
      'fr_FR',
    ]);

    expect(result[0].labels).toEqual({en_US: 'Nutrition'});
    expect(result[1].labels).toEqual({en_US: 'Per 100g', fr_FR: 'Pour 100g'});
    expect(getOptionLabels(result, 'nutrition', 'carbohydrate')).toEqual({en_US: 'Carbohydrate'});
    expect(getOptionLabels(result, 'nutrition', 'fat')).toEqual({en_US: 'Fat', fr_FR: 'Gros'});
    expect(getOptionLabels(result, 'nutrition', 'protein')).toEqual({});
  });

  it('should return nothing if template variation is unknown', async () => {
    const result = await getTranslatedTableConfigurationFromVariationTemplate('unknown', ['en_US', 'fr_FR']);
    expect(result).toEqual([]);
  });
});
