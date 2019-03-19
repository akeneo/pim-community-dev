import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {NormalizedOption} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import {NormalizedOptionCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option-collection';

const memo = (React as any).memo;

const OptionCollectionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const selectedOptionCollectionCode = value.data;
  const normalizedOptionCollectionAttribute = column.attribute as NormalizedOptionCollectionAttribute;
  const selectedOptionCollectionLabel = normalizedOptionCollectionAttribute.options
    .filter((option: NormalizedOption) => selectedOptionCollectionCode.includes(option.code))
    .map((selectedOption: NormalizedOption) => {
      return selectedOption.labels[column.locale] ? selectedOption.labels[column.locale] : `[${selectedOption.code}]`;
    })
    .join(', ');

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedOptionCollectionLabel}>
      {selectedOptionCollectionLabel}
    </div>
  );
});

export const cell = OptionCollectionCellView;
