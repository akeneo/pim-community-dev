import * as React from 'react';
import ItemView from 'akeneoassetmanager/application/component/asset-family/index/item';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetFamilyListItem, createEmptyAssetFamilyListItem} from 'akeneoassetmanager/domain/model/asset-family/list';

interface TableState {
  locale: string;
  assetFamilies: AssetFamilyListItem[];
  isLoading: boolean;
}

interface TableDispatch {
  onRedirectToAssetFamily: (assetFamily: AssetFamilyListItem) => void;
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
    assetFamilies: AssetFamilyListItem[],
    locale: string,
    isLoading: boolean,
    onRedirectToAssetFamily: (assetFamily: AssetFamilyListItem) => void
  ): JSX.Element | JSX.Element[] {
    if (0 === assetFamilies.length && isLoading) {
      const assetFamily = createEmptyAssetFamilyListItem();

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

    return assetFamilies.map((assetFamily: AssetFamilyListItem, index: number) => {
      const itemPosition = index - this.state.nextItemToAddPosition;

      return (
        <ItemView
          key={assetFamilyIdentifierStringValue(assetFamily.identifier)}
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
