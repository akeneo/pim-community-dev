import {TableConfiguration} from './TableConfiguration';
import {
  CompositionIcon,
  DimensionsIcon,
  FoodIcon,
  IconProps,
  ProductDimensionsIcon,
  TableIcon,
} from 'akeneo-design-system';
import React from 'react';

export type TemplateCode = string;

export type TemplateVariation = {
  code: TemplateCode;
  tableConfiguration: TableConfiguration;
};

export type Template = {
  code: string;
  icon: React.FC<IconProps>;
  template_variations: TemplateVariation[];
};

const nutritionTemplateEn: TemplateVariation = {
  code: 'nutrition-unitedkingdom',
  tableConfiguration: [
    {
      code: 'nutrition',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'energy', labels: {}},
        {code: 'fat', labels: {}},
        {code: 'of_which_saturates', labels: {}},
        {code: 'carbohydrate', labels: {}},
        {code: 'of_which_sugars', labels: {}},
        {code: 'fibre', labels: {}},
        {code: 'protein', labels: {}},
        {code: 'salt', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'per_100g',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'percentage_reference_intake',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'your_reference_intake',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
  ],
};

const nutritionTemplateEurope: TemplateVariation = {
  code: 'nutrition-europe',
  tableConfiguration: [
    {
      code: 'average_nutritional_values',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'energy', labels: {}},
        {code: 'fat', labels: {}},
        {code: 'saturated_fat', labels: {}},
        {code: 'carbohydrate', labels: {}},
        {code: 'sugars', labels: {}},
        {code: 'protein', labels: {}},
        {code: 'salt', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'per_100g',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'per_serving',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'percentage_reference_intake',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
  ],
};

const nutritionTemplateUnitedStates: TemplateVariation = {
  code: 'nutrition-unitedstates',
  tableConfiguration: [
    {
      code: 'nutrition_facts',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'total_fat', labels: {}},
        {code: 'saturated_fat', labels: {}},
        {code: 'trans_fat', labels: {}},
        {code: 'cholesterol', labels: {}},
        {code: 'sodium', labels: {}},
        {code: 'total_carbohydrate', labels: {}},
        {code: 'dietary_fiber', labels: {}},
        {code: 'total_sugars', labels: {}},
        {code: 'included_added_sugars', labels: {}},
        {code: 'protein', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'amount_per_serving',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'percentage_daily_value',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
  ],
};

const foodComposition: TemplateVariation = {
  code: 'food_composition',
  tableConfiguration: [
    {
      code: 'composition',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'wheat_flour', labels: {}},
        {code: 'butter', labels: {}},
        {code: 'water', labels: {}},
        {code: 'sugar', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'percentage',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
        max: 100,
      },
    },
    {
      code: 'allergen',
      data_type: 'boolean',
      labels: {},
      validations: {},
    },
  ],
};

const clothingComposition: TemplateVariation = {
  code: 'clothing_composition',
  tableConfiguration: [
    {
      code: 'composition',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'cotton', labels: {}},
        {code: 'polyester', labels: {}},
        {code: 'elastane', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'percentage',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
        max: 100,
      },
    },
  ],
};

const dimensionsMetric: TemplateVariation = {
  code: 'dimensions-metric',
  tableConfiguration: [
    {
      code: 'dimensions',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'length', labels: {}},
        {code: 'width', labels: {}},
        {code: 'height', labels: {}},
        {code: 'weight', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'value',
      data_type: 'number',
      labels: {},
      validations: {
        min: 0,
        decimals_allowed: true,
      },
    },
  ],
};

const dimensionsImperial: TemplateVariation = {
  code: 'dimensions-imperial',
  tableConfiguration: [
    {
      code: 'dimensions',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'length', labels: {}},
        {code: 'width', labels: {}},
        {code: 'height', labels: {}},
        {code: 'weight', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'value',
      data_type: 'number',
      labels: {},
      validations: {
        min: 0,
        decimals_allowed: true,
      },
    },
  ],
};

const packagingMetric: TemplateVariation = {
  code: 'packaging-metric',
  tableConfiguration: [
    {
      code: 'parcel',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'parcel1', labels: {}},
        {code: 'parcel2', labels: {}},
        {code: 'parcel3', labels: {}},
        {code: 'parcel4', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'width',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'length',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'height',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
  ],
};

const packagingImerial: TemplateVariation = {
  code: 'packaging-imperial',
  tableConfiguration: [
    {
      code: 'parcel',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'parcel1', labels: {}},
        {code: 'parcel2', labels: {}},
        {code: 'parcel3', labels: {}},
        {code: 'parcel4', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'width',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'length',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
    {
      code: 'height',
      data_type: 'number',
      labels: {},
      validations: {
        decimals_allowed: true,
        min: 0,
      },
    },
  ],
};

const emptyTable: TemplateVariation = {
  code: 'empty_table',
  tableConfiguration: [],
};

const TEMPLATES: Template[] = [
  {
    icon: FoodIcon,
    code: 'nutrition',
    template_variations: [nutritionTemplateEurope, nutritionTemplateUnitedStates, nutritionTemplateEn],
  },
  {
    icon: CompositionIcon,
    code: 'food_composition',
    template_variations: [foodComposition],
  },
  {
    icon: CompositionIcon,
    code: 'clothing_composition',
    template_variations: [clothingComposition],
  },
  {
    icon: ProductDimensionsIcon,
    code: 'dimensions',
    template_variations: [dimensionsMetric, dimensionsImperial],
  },
  {
    icon: DimensionsIcon,
    code: 'packaging',
    template_variations: [packagingMetric, packagingImerial],
  },
  {
    icon: TableIcon,
    code: 'empty_table',
    template_variations: [emptyTable],
  },
];

export {TEMPLATES};
