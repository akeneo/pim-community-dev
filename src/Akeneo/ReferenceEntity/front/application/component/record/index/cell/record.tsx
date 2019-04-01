import * as React from 'react';
import {NormalizedValue} from 'akeneoreferenceentity/domain/model/record/value';
import {CellView} from 'akeneoreferenceentity/application/configuration/value';
import {Column} from 'akeneoreferenceentity/application/reducer/grid';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const RecordCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const context = (value as any).context;

  const selectedRecordLabel = getLabel(context.labels[value.data], column.locale, value.data);

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedRecordLabel}>
      {selectedRecordLabel}
    </div>
  );
});

export const cell = RecordCellView;
