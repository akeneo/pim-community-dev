import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {NormalizedOption} from 'akeneoreferenceentity/domain/model/attribute/type/option/option';
import {NormalizedOptionCollectionAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/option-collection';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const OptionCollectionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const selectedOptionCollectionCodes = value.data;
  const normalizedOptionCollectionAttribute = column.attribute as NormalizedOptionCollectionAttribute;
  const selectedOptionCollectionLabels = normalizedOptionCollectionAttribute.options
    .filter((option: NormalizedOption) => selectedOptionCollectionCodes.includes(option.code))
    .map((selectedOption: NormalizedOption) => {
      return getLabel(selectedOption.labels, column.locale, selectedOption.code);
    })
    .join(', ');

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedOptionCollectionLabels}>
      {selectedOptionCollectionLabels}
    </div>
  );
});

export const cell = OptionCollectionCellView;
