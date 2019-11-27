import * as React from 'react';
import {getImageShowUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {AssetFamilyListItem, getAssetFamilyListItemLabel} from 'akeneoassetmanager/domain/model/asset-family/list';
import {isEmptyAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
const router = require('pim/router');

export default ({
  assetFamily,
  locale,
  isLoading = false,
  onRedirectToAssetFamily,
}: {
  assetFamily: AssetFamilyListItem;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToAssetFamily: (assetFamily: AssetFamilyListItem) => void;
}) => {
  const path = !isEmptyAssetFamilyIdentifier(assetFamily.identifier)
    ? `#${router.generate('akeneo_asset_manager_asset_family_edit', {
        identifier: assetFamily.identifier,
        tab: 'asset',
      })}`
    : '';

  return (
    <a
      href={path}
      title={getAssetFamilyListItemLabel(assetFamily, locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? 'AknLoadingPlaceHolder' : ''
      }`}
      data-identifier={assetFamily.identifier}
      onClick={event => {
        event.preventDefault();

        onRedirectToAssetFamily(assetFamily);

        return false;
      }}
    >
      <span
        className="AknGrid-fullImage"
        style={{
          backgroundImage: `url("${getImageShowUrl(assetFamily.image, 'thumbnail')}")`,
        }}
      />
      <span className="AknGrid-title">{getAssetFamilyListItemLabel(assetFamily, locale)}</span>
      <span className="AknGrid-subTitle">{assetFamily.identifier}</span>
      <span className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox" />
      <span className="AknGrid-bodyCell AknGrid-bodyCell--actions">
        <div className="AknButtonList AknButtonList--right AknButtonList--expanded" />
      </span>
    </a>
  );
};
