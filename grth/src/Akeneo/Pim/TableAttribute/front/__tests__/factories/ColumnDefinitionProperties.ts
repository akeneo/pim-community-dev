import {ColumnDefinitionPropertiesMapping} from '../../src/attribute/ColumDefinitionProperties';
import SelectProperties from '../../src/attribute/ColumDefinitionProperties/SelectProperties';
import NumberProperties from '../../src/attribute/ColumDefinitionProperties/NumberProperties';
import TextProperties from '../../src/attribute/ColumDefinitionProperties/TextProperties';

export const columnDefinitionPropertiesMapping: ColumnDefinitionPropertiesMapping = {
  select: {default: SelectProperties},
  number: {default: NumberProperties},
  text: {default: TextProperties},
};
