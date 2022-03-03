import React from 'react';
import ReactDOM from 'react-dom';
import {AssetCollectionCell} from './AssetCollectionCell';
const StringCell = require('oro/datagrid/string-cell');

class AssetMultipleLinkCell extends StringCell {
  render() {
    const assetMulti = this.formatter.fromRaw(this.model.get(this.column.get('name')));

    if (!assetMulti) {
      return this;
    }

    ReactDOM.render(<AssetCollectionCell attributeIdentifier={assetMulti.attribute} data={assetMulti.data} />, this.el);

    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = AssetMultipleLinkCell;
