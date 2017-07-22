/* global define */
import StringCell from 'oro/datagrid/string-cell';
import MediaUrlGenerator from 'pim/media-url-generator';


/**
 * Image column cell
 *
 * @extends oro.datagrid.StringCell
 */
export default StringCell.extend({
  /**
   * Render an image.
   */
  render: function() {
    var image = this.formatter.fromRaw(this.model.get(this.column.get("name")));

    var src = MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail_small');
    this.$el.empty().html('<img src="' + src + '" title="' + image.originalFilename + '" />');

    return this;
  }
});

