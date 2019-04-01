import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const RecordCollectionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const context = (value as any).context;

  const selectedRecordCollectionLabels = value.data
    .map((recordCode: string) => getLabel(context.labels[recordCode], column.locale, recordCode))
    .join(', ');

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedRecordCollectionLabels}>
      {selectedRecordCollectionLabels}
    </div>
  );
});

export const cell = RecordCollectionCellView;
