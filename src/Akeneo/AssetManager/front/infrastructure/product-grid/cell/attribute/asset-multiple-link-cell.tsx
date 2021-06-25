import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';

const StringCell = require('oro/datagrid/string-cell');
const Template = require('pim/template/datagrid/cell/image-cell');
const _ = require('underscore');
const routing = require('routing');

class AssetMultipleLinkCell extends StringCell {
  render() {
    const assetMulti = this.formatter.fromRaw(this.model.get(this.column.get('name')));

    if (!assetMulti) {
      return this;
    }

    const src = getMediaPreviewUrl(routing, {
      type: MediaPreviewType.Thumbnail,
      attributeIdentifier: assetMulti.attribute,
      data: getMediaData(assetMulti.data),
    });

    this.$el.empty().html(_.template(Template)({label: '', src}));

    return this;
  }
}

export = AssetMultipleLinkCell;
