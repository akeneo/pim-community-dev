import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {AssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {
  assetTypeAreEqual,
  assetTypeStringValue,
  assetTypeIsEmpty,
} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

type Props = {
  attribute: AssetAttribute;
  errors: ValidationError[];
  locale: string;
};

class AssetView extends React.Component<Props, {assetFamily: AssetFamily | null}> {
  state = {assetFamily: null};
  async componentDidMount() {
    this.updateAssetFamily();
  }

  async componentDidUpdate(prevProps: Props) {
    if (!assetTypeAreEqual(this.props.attribute.getAssetType(), prevProps.attribute.getAssetType())) {
      this.updateAssetFamily();
    }
  }

  async updateAssetFamily() {
    if (!assetTypeIsEmpty(this.props.attribute.assetType)) {
      const assetFamilyResult = await assetFamilyFetcher.fetch(this.props.attribute.assetType);
      this.setState({assetFamily: assetFamilyResult.assetFamily});
    }
  }

  render() {
    const value =
      null !== this.state.assetFamily
        ? (this.state.assetFamily as any).getLabel(this.props.locale)
        : assetTypeStringValue(this.props.attribute.assetType);

    return (
      <React.Fragment>
        <div className="AknFieldContainer" data-code="assetType">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.asset_type">
              {__('pim_asset_manager.attribute.edit.input.asset_type')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              autoComplete="off"
              className="AknTextField AknTextField--light AknTextField--disabled"
              id="pim_asset_manager.attribute.edit.input.asset_type"
              name="asset_type"
              value={value}
              readOnly
              tabIndex={-1}
            />
          </div>
          {getErrorsView(this.props.errors, 'assetType')}
        </div>
      </React.Fragment>
    );
  }
}

export const view = AssetView;
