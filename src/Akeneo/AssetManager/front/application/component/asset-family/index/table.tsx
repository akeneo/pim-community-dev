import * as React from 'react';
import ItemView from 'akeneoassetmanager/application/component/asset-family/index/item';
import AssetFamily, {createAssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createEmptyFile} from 'akeneoassetmanager/domain/model/file';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';

interface TableState {
  locale: string;
  assetFamilies: AssetFamily[];
  isLoading: boolean;
}

interface TableDispatch {
  onRedirectToAssetFamily: (assetFamily: AssetFamily) => void;
}

interface TableProps extends TableState, TableDispatch {}

export default class Table extends React.Component<TableProps, {nextItemToAddPosition: number}> {
  readonly state = {
    nextItemToAddPosition: 0,
  };

  componentDidUpdate(previousProps: TableProps) {
    if (this.props.assetFamilies.length !== previousProps.assetFamilies.length) {
      this.setState({nextItemToAddPosition: previousProps.assetFamilies.length});
    }
  }

  renderItems(
    assetFamilies: AssetFamily[],
    locale: string,
    isLoading: boolean,
    onRedirectToAssetFamily: (assetFamily: AssetFamily) => void
  ): JSX.Element | JSX.Element[] {
    if (0 === assetFamilies.length && isLoading) {
      const labelCollection = createLabelCollection({});
      const assetFamily = createAssetFamily(
        '',
        labelCollection,
        createEmptyFile(),
        '',
        ''
      );

      return Array(4)
        .fill('placeholder')
        .map((attributeIdentifier, key) => (
          <ItemView
            key={`${attributeIdentifier}_${key}`}
            isLoading={isLoading}
            assetFamily={assetFamily}
            locale={locale}
            onRedirectToAssetFamily={() => {}}
            position={key}
          />
        ));
    }

    return assetFamilies.map((assetFamily: AssetFamily, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <ItemView
          key={assetFamilyIdentifierStringValue(assetFamily.getIdentifier())}
          assetFamily={assetFamily}
          locale={locale}
          onRedirectToAssetFamily={onRedirectToAssetFamily}
          position={itemPosition > 0 ? itemPosition : 0}
        />
      );
    });
  }

  render(): JSX.Element | JSX.Element[] {
    const {assetFamilies, locale, onRedirectToAssetFamily, isLoading} = this.props;

    return (
      <div className="AknGrid">
        <div className="AknGrid-body">
          {this.renderItems(assetFamilies, locale, isLoading, onRedirectToAssetFamily)}
        </div>
      </div>
    );
  }
}
