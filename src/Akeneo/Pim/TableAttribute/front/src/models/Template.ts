import {TableConfiguration} from './TableConfiguration';
import {FoodIcon, IconProps, TableIcon} from 'akeneo-design-system';
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
  code: 'nutrition-en',
  tableConfiguration: [
    {
      code: 'type',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'calories', labels: {}},
        {code: 'fat', labels: {}},
        {code: 'cholesterol', labels: {}},
        {code: 'sodium', labels: {}},
        {code: 'carbohydrate', labels: {}},
        {code: 'protein', labels: {}},
      ],
      validations: [],
    },
    {
      code: 'quantity',
      data_type: 'text',
      labels: {},
      validations: {},
    },
  ],
};

const nutritionTemplateEu: TemplateVariation = {
  code: 'nutrition-eu',
  tableConfiguration: [
    {
      code: 'type',
      data_type: 'select',
      labels: {},
      options: [
        {code: 'energy', labels: {}},
        {code: 'fat', labels: {}},
        {code: 'cholesterol', labels: {}},
        {code: 'salt', labels: {}},
      ],
      validations: {},
    },
    {
      code: 'quantity',
      data_type: 'text',
      labels: {},
      validations: {},
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
    template_variations: [nutritionTemplateEn, nutritionTemplateEu],
  },
  {
    icon: TableIcon,
    code: 'empty_table',
    template_variations: [emptyTable],
  },
];

export {TEMPLATES};
