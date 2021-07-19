import {TableConfiguration} from './TableConfiguration';
import {IconProps, TableIcon} from 'akeneo-design-system';
import React from 'react';

export type TemplateCode = string;

export type Template = {
  code: TemplateCode;
  tableConfiguration: TableConfiguration;
  icon: React.FC<IconProps>;
};

/**
 * TODO When we will add real templates with labels, we will have to ensure that we will remove the labels which
 * are not on catalog locales list, to be able to save without errors
 */
const TEMPLATES: Template[] = [
  {
    code: 'empty_table',
    tableConfiguration: [],
    icon: TableIcon,
  }
];

export {TEMPLATES};
