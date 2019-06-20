import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {NormalizedOption} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {NormalizedOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const OptionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const selectedOptionCode = value.data;
  const normalizedOptionAttribute = column.attribute as NormalizedOptionAttribute;
  const selectedOption = normalizedOptionAttribute.options.filter(
    (option: NormalizedOption) => option.code === selectedOptionCode
  );

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
