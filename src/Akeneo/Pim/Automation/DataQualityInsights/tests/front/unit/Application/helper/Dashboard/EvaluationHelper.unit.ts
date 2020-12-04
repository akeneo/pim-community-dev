import {convertEvaluationToLegacyFormat} from '@akeneo-pim-community/data-quality-insights/src/application/helper';

const evaluation = {
  ecommerce: {
    en_US: [
      {
        code: 'completeness_of_required_attributes',
        rate: {value: 80, rank: 'B'},
        improvable_attributes: ['price'],
        status: 'done',
      },
      {
        code: 'completeness_of_non_required_attributes',
        rate: {value: 70, rank: 'C'},
        improvable_attributes: ['picture'],
        status: 'done',
      },
      {
        code: 'enrichment_image',
        rate: {value: 0, rank: 'E'},
        improvable_attributes: ['picture'],
        status: 'done',
      },
      {
        code: 'consistency_textarea_uppercase_words',
        rate: {value: 100, rank: 'A'},
        improvable_attributes: [],
        status: 'done',
      },
      {
        code: 'consistency_textarea_lowercase_words',
        rate: {value: 76, rank: 'C'},
        improvable_attributes: ['description'],
        status: 'done',
      },
      {
        code: 'consistency_spelling',
        rate: {value: 88, rank: 'B'},
        improvable_attributes: ['description', 'title'],
        status: 'done',
      },
      {
        code: 'consistency_attribute_spelling',
        rate: {value: null, rank: null},
        improvable_attributes: [],
        status: 'not_applicable',
      },
      {
        code: 'consistency_attribute_option_spelling',
        rate: {value: null, rank: null},
        improvable_attributes: [],
        status: 'not_applicable',
      },
    ],
  },
};

const axes = {
  consistency: [
    'consistency_spelling',
    'consistency_textarea_lowercase_words',
    'consistency_textarea_uppercase_words',
    'consistency_text_title_formatting',
    'consistency_attribute_spelling',
    'consistency_attribute_option_spelling',
  ],
  enrichment: ['completeness_of_non_required_attributes', 'completeness_of_required_attributes', 'enrichment_image'],
};

test('convert an evaluation from the new format to the legacy format', () => {
  const expectedEvaluation = {
    consistency: {
      ecommerce: {
        en_US: {
          rate: null,
          criteria: [
            {
              code: 'consistency_textarea_uppercase_words',
              rate: {value: 100, rank: 'A'},
              improvable_attributes: [],
              status: 'done',
            },
            {
              code: 'consistency_textarea_lowercase_words',
              rate: {value: 76, rank: 'C'},
              improvable_attributes: ['description'],
              status: 'done',
            },
            {
              code: 'consistency_spelling',
              rate: {value: 88, rank: 'B'},
              improvable_attributes: ['description', 'title'],
              status: 'done',
            },
            {
              code: 'consistency_attribute_spelling',
              rate: {value: null, rank: null},
              improvable_attributes: [],
              status: 'not_applicable',
            },
            {
              code: 'consistency_attribute_option_spelling',
              rate: {value: null, rank: null},
              improvable_attributes: [],
              status: 'not_applicable',
            },
          ],
        },
      },
    },
    enrichment: {
      ecommerce: {
        en_US: {
          rate: null,
          criteria: [
            {
              code: 'completeness_of_required_attributes',
              rate: {value: 80, rank: 'B'},
              improvable_attributes: ['price'],
              status: 'done',
            },
            {
              code: 'completeness_of_non_required_attributes',
              rate: {value: 70, rank: 'C'},
              improvable_attributes: ['picture'],
              status: 'done',
            },
            {
              code: 'enrichment_image',
              rate: {value: 0, rank: 'E'},
              improvable_attributes: ['picture'],
              status: 'done',
            },
          ],
        },
      },
    },
  };
  expect(convertEvaluationToLegacyFormat(axes, evaluation)).toStrictEqual(expectedEvaluation);
});
