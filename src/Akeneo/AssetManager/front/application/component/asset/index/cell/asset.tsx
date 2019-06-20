import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const AssetCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const context = (value as any).context;

  if (0 === context.labels.length) {
    return null;
  }

  const selectedAssetLabel = getLabel(
    context.labels[value.data].labels,
    column.locale,
    context.labels[value.data].code
  );

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedAssetLabel}>
      {selectedAssetLabel}
    </div>
  );
});

export const cell = AssetCellView;
