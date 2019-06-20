import * as React from 'react';
import {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {Column} from 'akeneoassetmanager/application/reducer/grid';
import {CellViews} from 'akeneoassetmanager/application/component/asset-family/edit/asset';

const memo = (React as any).memo;

const DetailRow = memo(
  ({
    asset,
    placeholder = false,
    onRedirectToAsset,
    columns,
    cellViews,
  }: {
    asset: NormalizedAsset;
    placeholder?: boolean;
    position: number;
    columns: Column[];
    cellViews: CellViews;
  } & {
    onRedirectToAsset: (asset: NormalizedAsset) => void;
  }) => {
    if (true === placeholder) {
      return (
        <tr className="AknGrid-bodyRow">
          {columns.map((colum: Column) => {
            return (
              <td key={colum.key} className="AknGrid-bodyCell">
                <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
              </td>
            );
          })}
        </tr>
      );
    }

    return (
      <tr
        className="AknGrid-bodyRow"
        data-identifier={asset.identifier}
        onClick={event => {
          event.preventDefault();

          onRedirectToAsset(asset);

          return false;
        }}
      >
        {0 === columns.length ? <td className="AknGrid-bodyCell" /> : null}

        {columns.map((column: Column) => {
          const CellView = cellViews[column.key];
          const value = asset.values[column.key as any];

          if (undefined === value) {
            return <td key={column.key} className="AknGrid-bodyCell" />;
          }

          return (
            <td key={column.key} className="AknGrid-bodyCell">
              <CellView column={column} value={value} />
            </td>
          );
        })}
      </tr>
    );
  }
);

const DetailRows = memo(
  ({
    assets,
    locale,
    placeholder,
    onRedirectToAsset,
    assetCount,
    columns,
    cellViews,
  }: {
    assets: NormalizedAsset[];
    locale: string;
    placeholder: boolean;
    onRedirectToAsset: (asset: NormalizedAsset) => void;
    assetCount: number;
    columns: Column[];
    cellViews: CellViews;
  }) => {
    if (placeholder) {
      const asset = {
        identifier: '',
        asset_family_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
      };

      const placeholderCount = assetCount < 30 ? assetCount : 30;

      return Array.from(Array(placeholderCount).keys()).map(key => (
        <DetailRow
          placeholder={placeholder}
          key={key}
          asset={asset}
          locale={locale}
          onRedirectToAsset={() => {}}
          columns={columns}
          cellViews={cellViews}
        />
      ));
    }

    return assets.map((asset: NormalizedAsset) => {
      return (
        <DetailRow
          placeholder={false}
          key={asset.identifier}
          asset={asset}
          locale={locale}
          onRedirectToAsset={onRedirectToAsset}
          columns={columns}
          cellViews={cellViews}
        />
      );
    });
  }
);

export default DetailRows;
