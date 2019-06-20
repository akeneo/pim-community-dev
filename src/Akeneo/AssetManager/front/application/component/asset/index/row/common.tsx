import * as React from 'react';
import {NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {getImageShowUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {denormalizeFile} from 'akeneoassetmanager/domain/model/file';
import {getLabel} from 'pimui/js/i18n';
import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import CompletenessLabel from 'akeneoassetmanager/application/component/app/completeness';

const memo = (React as any).memo;

const CommonRow = memo(
  ({
    asset,
    locale,
    placeholder = false,
    onRedirectToAsset,
  }: {
    asset: NormalizedItemAsset;
    locale: string;
    placeholder?: boolean;
  } & {
    onRedirectToAsset: (asset: NormalizedItemAsset) => void;
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
            src={getImageShowUrl(denormalizeFile(asset.image), 'thumbnail_small')}
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
    locale,
    placeholder,
    onRedirectToAsset,
    assetCount,
  }: {
    assets: NormalizedItemAsset[];
    locale: string;
    placeholder: boolean;
    onRedirectToAsset: (asset: NormalizedItemAsset) => void;
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
        <CommonRow placeholder={placeholder} key={key} asset={asset} locale={locale} onRedirectToAsset={() => {}} />
      ));
    }

    return assets.map((asset: NormalizedItemAsset) => {
      return (
        <CommonRow
          placeholder={false}
          key={asset.identifier}
          asset={asset}
          locale={locale}
          onRedirectToAsset={onRedirectToAsset}
        />
      );
    });
  }
);

export default CommonRows;
