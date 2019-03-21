import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {NormalizedOption} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import {NormalizedOptionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const OptionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const selectedOptionCode = value.data;
  const normalizedOptionAttribute = column.attribute as NormalizedOptionAttribute;
  const selectedOption = normalizedOptionAttribute.options.filter(
    (option: NormalizedOption) => option.code === selectedOptionCode
  );
  // const selectedOptionLabel =
  //   selectedOption !== undefined && selectedOption.labels[column.locale]
  //     ? selectedOption.labels[column.locale]
  //     : `[${selectedOptionCode}]`;
  const selectedOptionLabel = getLabel(selectedOption[0].labels, column.locale, selectedOption[0].code);

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedOptionLabel}>
      {selectedOptionLabel}
    </div>
  );
});

export const cell = OptionCellView;
