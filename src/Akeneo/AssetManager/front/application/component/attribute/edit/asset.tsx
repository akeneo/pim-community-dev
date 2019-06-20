import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {AssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import AssetFamily from 'akeneoassetmanager/domain/model/asset-family/asset-family';

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
    if (!this.props.attribute.getAssetType().equals(prevProps.attribute.getAssetType())) {
      this.updateAssetFamily();
    }
  }

  async updateAssetFamily() {
    const assetFamilyResult = await assetFamilyFetcher.fetch(
      this.props.attribute.assetType.getAssetFamilyIdentifier()
    );
    this.setState({assetFamily: assetFamilyResult.assetFamily});
  }

  render() {
    const value =
      null !== this.state.assetFamily
        ? (this.state.assetFamily as any).getLabel(this.props.locale)
        : this.props.attribute.assetType.stringValue();

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
