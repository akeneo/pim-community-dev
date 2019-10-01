import * as React from 'react';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {getImageShowUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {assetFamilyidentifiersAreEqual, denormalizeAssetFamilyIdentifier, assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
const router = require('pim/router');

export default ({
  assetFamily,
  locale,
  isLoading = false,
  onRedirectToAssetFamily,
}: {
  assetFamily: AssetFamily;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToAssetFamily: (assetFamily: AssetFamily) => void;
}) => {
  const path =
    !assetFamilyidentifiersAreEqual(assetFamily.getIdentifier(), denormalizeAssetFamilyIdentifier(''))
      ? `#${router.generate('akeneo_asset_manager_asset_family_edit', {
          identifier: assetFamilyIdentifierStringValue(assetFamily.getIdentifier()),
          tab: 'asset',
        })}`
      : '';

  return (
    <a
      href={path}
      title={assetFamily.getLabel(locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? 'AknLoadingPlaceHolder' : ''
      }`}
      data-identifier={assetFamilyIdentifierStringValue(assetFamily.getIdentifier())}
      onClick={event => {
        event.preventDefault();

        onRedirectToAssetFamily(assetFamily);

        return false;
      }}
    >
      <span
        className="AknGrid-fullImage"
        style={{
          backgroundImage: `url("${getImageShowUrl(assetFamily.getImage(), 'thumbnail')}")`,
        }}
      />
      <span className="AknGrid-title">{assetFamily.getLabel(locale)}</span>
      <span className="AknGrid-subTitle">{assetFamilyIdentifierStringValue(assetFamily.getIdentifier())}</span>
      <span className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox" />
      <span className="AknGrid-bodyCell AknGrid-bodyCell--actions">
        <div className="AknButtonList AknButtonList--right AknButtonList--expanded" />
      </span>
    </a>
  );
};
