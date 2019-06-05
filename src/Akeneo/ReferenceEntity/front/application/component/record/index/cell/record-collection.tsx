import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const RecordCollectionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const context = (value as any).context;

  if (0 === context.labels.length) {
    return null;
  }

  const selectedRecordCollectionLabels = value.data
    .map((recordIdentifier: string) =>
      getLabel(context.labels[recordIdentifier].labels, column.locale, context.labels[recordIdentifier].code)
    )
    .join(', ');

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedRecordCollectionLabels}>
      {selectedRecordCollectionLabels}
    </div>
  );
});

export const cell = RecordCollectionCellView;
