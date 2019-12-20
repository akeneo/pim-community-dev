import * as React from 'react';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {isOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {getLabel} from 'pimui/js/i18n';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {isOptionCollectionData} from 'akeneoassetmanager/domain/model/asset/data/option-collection';

const memo = (React as any).memo;

const OptionCollectionCellView: CellView = memo(({column, value}: {column: Column; value: EditionValue}) => {
  if (!isOptionCollectionData(value.data)) return null;
  if (!isOptionCollectionAttribute(column.attribute)) return null;

  const data = value.data;

  const selectedOptionCollectionLabels =
    null === value.data
      ? ''
      : column.attribute.options
          .filter((option: Option) => null !== data && data.includes(option.code))
          .map((selectedOption: Option) => {
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
