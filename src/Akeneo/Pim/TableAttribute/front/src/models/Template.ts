import {TableConfiguration} from './TableConfiguration';
import {FoodIcon, IconProps, TableIcon} from 'akeneo-design-system';
import React from 'react';

export type TemplateCode = string;

export type LocalizedTemplate = {
  code: TemplateCode;
  tableConfiguration: TableConfiguration;
};

export type Template = {
  code: string;
  icon: React.FC<IconProps>;
  templates: LocalizedTemplate[];
};

/**
 * TODO When we will add real templates with labels, we will have to ensure that we will remove the labels which
 * are not on catalog locales list, to be able to save without errors
 */

const nutritionTemplateUs: LocalizedTemplate = {
  code: 'nutrition-en',
  tableConfiguration: [
    {
      code: 'type',
      data_type: 'select',
      labels: {en_US: 'Type'},
      options: [
        {code: 'calories', labels: {en_US: 'Calories'}},
        {code: 'fat', labels: {en_US: 'Fat'}},
        {code: 'cholesterol', labels: {en_US: 'Cholesterol'}},
        {code: 'sodium', labels: {en_US: 'Sodium'}},
        {code: 'carbohydrate', labels: {en_US: 'Carbohydrate'}},
        {code: 'protein', labels: {en_US: 'Protein'}},
      ],
      validations: [],
    },
    {
      code: 'quantity',
      data_type: 'text',
      labels: {en_US: 'Quantity'},
      validations: {},
    },
  ],
};

const nutitionTemplateEu: LocalizedTemplate = {
  code: 'nutrition-eu',
  tableConfiguration: [
    {
      code: 'type',
      data_type: 'select',
      labels: {en_US: 'Type'},
      options: [
        {code: 'energy', labels: {en_US: 'Energy'}},
        {code: 'fat', labels: {en_US: 'Fat'}},
        {code: 'cholesterol', labels: {en_US: 'Cholesterol'}},
        {code: 'salt', labels: {en_US: 'Salt'}},
      ],
      validations: {},
    },
    {
      code: 'quantity',
      data_type: 'text',
      labels: {en_US: 'Quantity'},
      validations: {},
    },
  ],
};

const emptyTable: LocalizedTemplate = {
  code: 'empty_table',
  tableConfiguration: [],
};

const TEMPLATES: Template[] = [
  {
    icon: FoodIcon,
    code: 'nutrition',
    templates: [nutritionTemplateUs, nutitionTemplateEu],
  },
  {
    icon: TableIcon,
    code: 'empty_table',
    templates: [emptyTable],
  },
];

export {TEMPLATES};
