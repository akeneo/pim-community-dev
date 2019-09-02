import * as React from 'react';
import {NormalizedValue} from 'akeneoassetmanager/domain/model/asset/value';
import {CellView} from 'akeneoassetmanager/application/configuration/value';

const memo = (React as any).memo;

const MediaLinkCellView: CellView = memo(({value}: {value: NormalizedValue}) => {
  const mediaLink = undefined === value ? '' : value.data;

  return (
    <div className="AknGrid-bodyCellContainer" title={mediaLink}>
      <img className="AknGrid-image AknLoadingPlaceHolder" width="44" height="44" src={mediaLink} />
    </div>
  );
});

export const cell = MediaLinkCellView;
