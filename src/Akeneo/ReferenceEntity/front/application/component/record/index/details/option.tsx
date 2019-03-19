import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {NormalizedOption} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import {NormalizedOptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';

const memo = (React as any).memo;

const OptionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const selectedOptionCode = value.data;
  const normalizedOptionAttribute = column.attribute as NormalizedOptionAttribute;
  const selectedOption = normalizedOptionAttribute.options.find(
    (option: NormalizedOption) => option.code === selectedOptionCode
  );
  const selectedOptionLabel =
    selectedOption !== undefined && selectedOption.labels[column.locale]
      ? selectedOption.labels[column.locale]
      : `[${selectedOptionCode}]`;

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedOptionLabel}>
      {selectedOptionLabel}
    </div>
  );
});

export const cell = OptionCellView;
