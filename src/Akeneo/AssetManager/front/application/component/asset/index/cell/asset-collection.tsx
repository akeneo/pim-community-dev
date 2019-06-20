import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {CellView} from 'akeneoassetmanager/application/configuration/value';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {getLabel} from 'pimui/js/i18n';

const memo = (React as any).memo;

const AssetCollectionCellView: CellView = memo(({column, value}: {column: Column; value: NormalizedValue}) => {
  const context = (value as any).context;

  if (0 === context.labels.length) {
    return null;
  }

  const selectedAssetCollectionLabels = value.data
    .map((assetIdentifier: string) =>
      getLabel(context.labels[assetIdentifier].labels, column.locale, context.labels[assetIdentifier].code)
    )
    .join(', ');

  return (
    <div className="AknGrid-bodyCellContainer" title={selectedAssetCollectionLabels}>
      {selectedAssetCollectionLabels}
    </div>
  );
});

export const cell = AssetCollectionCellView;
