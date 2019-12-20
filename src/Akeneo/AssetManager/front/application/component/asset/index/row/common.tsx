import * as React from 'react';
import {getLabel} from 'pimui/js/i18n';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import CompletenessLabel from 'akeneoassetmanager/application/component/app/completeness';
import {getAssetPreview, MediaPreviewTypes} from 'akeneoassetmanager/tools/media-url-generator';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import ListAsset from 'akeneoassetmanager/domain/model/asset/list-asset';

const memo = (React as any).memo;

const CommonRow = memo(
  ({
    asset,
    locale,
    channel,
    placeholder = false,
    onRedirectToAsset,
  }: {
    asset: ListAsset;
    locale: string;
    channel: string;
    placeholder?: boolean;
  } & {
    onRedirectToAsset: (asset: ListAsset) => void;
  }) => {
    if (true === placeholder) {
      return (
        <tr>
          <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
          <td className="AknGrid-bodyCell" colSpan={3}>
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
        </tr>
      );
    }

    const label = getLabel(asset.labels, locale, asset.code);

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
        <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
          <img
            className="AknGrid-image AknLoadingPlaceHolder"
            width="44"
            height="44"
            src={getAssetPreview(asset, MediaPreviewTypes.Thumbnail, {locale, channel})}
          />
        </td>
        <td className="AknGrid-bodyCell" title={label}>
          {label}
        </td>
        <td className="AknGrid-bodyCell AknGrid-bodyCell--identifier" title={asset.code}>
          {asset.code}
        </td>
        <td className="AknGrid-bodyCell">
          <CompletenessLabel completeness={Completeness.createFromNormalized(asset.completeness)} expanded={false} />
        </td>
      </tr>
    );
  }
);

const CommonRows = memo(
  ({
    assets,
    assetFamily,
    locale,
    channel,
    placeholder,
    onRedirectToAsset,
    assetCount,
  }: {
    assets: ListAsset[];
    assetFamily: AssetFamily;
    locale: string;
    channel: string;
    placeholder: boolean;
    onRedirectToAsset: (asset: ListAsset) => void;
    nextItemToAddPosition: number;
    assetCount: number;
  }) => {
    if (placeholder) {
      const asset = {
        identifier: '',
        asset_family_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
        completeness: {},
      };

      const placeholderCount = assetCount < 30 ? assetCount : 30;

      return Array.from(Array(placeholderCount).keys()).map(key => (
        <CommonRow
          placeholder={placeholder}
          key={key}
          asset={asset}
          assetFamily={assetFamily}
          locale={locale}
          channel={channel}
          onRedirectToAsset={() => {}}
        />
      ));
    }

    return assets.map((asset: ListAsset) => {
      return (
        <CommonRow
          placeholder={false}
          key={asset.identifier}
          asset={asset}
          assetFamily={assetFamily}
          locale={locale}
          channel={channel}
          onRedirectToAsset={onRedirectToAsset}
        />
      );
    });
  }
);

export default CommonRows;
