import * as React from 'react';
import {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {getLabel} from 'pimui/js/i18n';
import Key from 'akeneoassetmanager/tools/key';
const router = require('pim/router');

const memo = (React as any).memo;

const ActionRow = memo(
  ({
    asset,
    locale,
    placeholder = false,
    rights,
    onRedirectToAsset,
    onDeleteAsset,
  }: {
    asset: NormalizedAsset;
    locale: string;
    placeholder?: boolean;
    rights: {
      asset: {
        edit: boolean;
        delete: boolean;
      };
    };
  } & {
    onRedirectToAsset: (asset: NormalizedAsset) => void;
    onDeleteAsset: (assetCode: AssetCode, label: string) => void;
  }) => {
    if (true === placeholder) {
      return (
        <tr>
          <td className="AknGrid-bodyCell">
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
        </tr>
      );
    }

    const path =
      '' !== asset.identifier
        ? `#${router.generate('akeneo_asset_manager_asset_edit', {
            assetFamilyIdentifier: asset.asset_family_identifier,
            assetCode: asset.code,
            tab: 'enrich',
          })}`
        : '';

    const label = getLabel(asset.labels, locale, asset.code);
    const accesButtonClassName = rights.asset.edit
      ? 'AknIconButton AknIconButton--small AknIconButton--edit AknButtonList-item'
      : 'AknIconButton AknIconButton--small AknIconButton--view AknButtonList-item';

    return (
      <tr
        className={`AknGrid-bodyRow ${placeholder ? 'AknLoadingPlaceHolder' : ''}`}
        data-identifier={asset.identifier}
      >
        <td className="AknGrid-bodyCell AknGrid-bodyCell--action">
          <div className="AknButtonList AknButtonList--right AknButtonList--expanded">
            <a
              tabIndex={0}
              href={path}
              onKeyPress={(event: React.KeyboardEvent<HTMLAnchorElement>) => {
                event.preventDefault();
                if (Key.Space === event.key) onRedirectToAsset(asset);

                return false;
              }}
              className={accesButtonClassName}
              data-identifier={asset.identifier}
              onClick={event => {
                event.preventDefault();

                onRedirectToAsset(asset);

                return false;
              }}
            />
            {rights.asset.delete ? (
              <span
                tabIndex={0}
                onKeyPress={(event: React.KeyboardEvent<HTMLAnchorElement>) => {
                  event.preventDefault();

                  onDeleteAsset(AssetCode.create(asset.code), label);

                  return false;
                }}
                className="AknIconButton AknIconButton--small AknIconButton--trash AknButtonList-item"
                data-identifier={asset.identifier}
                onClick={event => {
                  event.preventDefault();

                  onDeleteAsset(AssetCode.create(asset.code), label);

                  return false;
                }}
              />
            ) : null}
          </div>
        </td>
      </tr>
    );
  }
);

const ActionRows = memo(
  ({
    assets,
    locale,
    placeholder,
    onRedirectToAsset,
    onDeleteAsset,
    assetCount,
    rights,
  }: {
    assets: NormalizedAsset[];
    locale: string;
    placeholder: boolean;
    onRedirectToAsset: (asset: NormalizedAsset) => void;
    onDeleteAsset: (assetCode: AssetCode, label: string) => void;
    assetCount: number;
    rights: {
      asset: {
        edit: boolean;
        delete: boolean;
      };
    };
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
        <ActionRow
          placeholder={true}
          key={key}
          asset={asset}
          locale={locale}
          onRedirectToAsset={() => {}}
          onDeleteAsset={() => {}}
          rights={rights}
        />
      ));
    }

    return assets.map((asset: NormalizedAsset) => {
      return (
        <ActionRow
          placeholder={false}
          key={asset.identifier}
          asset={asset}
          locale={locale}
          onRedirectToAsset={onRedirectToAsset}
          onDeleteAsset={onDeleteAsset}
          rights={rights}
        />
      );
    });
  }
);

export default ActionRows;
