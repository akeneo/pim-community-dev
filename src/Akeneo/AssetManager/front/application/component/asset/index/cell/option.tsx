import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {isOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {getLabel} from 'pimui/js/i18n';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {isOptionData} from 'akeneoassetmanager/domain/model/asset/data/option';

const memo = (React as any).memo;

const OptionCellView: CellView = memo(({column, value}: {column: Column; value: EditionValue}) => {
  if (!isOptionData(value.data)) return null;
  if (!isOptionAttribute(column.attribute)) return null;

  const selectedOptionCode = value.data;
  const selectedOption = column.attribute.options.filter((option: Option) => option.code === selectedOptionCode);

  if (0 === selectedOption.length) {
    return null;
  }

  const selectedOptionLabel = getLabel(selectedOption[0].labels, column.locale, selectedOption[0].code);

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedOptionLabel}>
      {selectedOptionLabel}
    </div>
  );
});

export const cell = OptionCellView;
