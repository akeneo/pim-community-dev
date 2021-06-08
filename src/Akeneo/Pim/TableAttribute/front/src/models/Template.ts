import { TableConfiguration } from "./TableConfiguration";
import { AddAttributeIcon, IconProps, TableIcon } from "akeneo-design-system";
import React from "react";

export type Template = {
  code: string;
  tableConfiguration: TableConfiguration;
  icon: React.FC<IconProps>;
}

const templates: Template[] = [
  {
    code: 'empty_table',
    tableConfiguration: [],
    icon: TableIcon,
  }
]

export { templates };
