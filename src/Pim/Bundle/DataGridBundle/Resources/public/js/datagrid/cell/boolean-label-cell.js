/* global define */
import StringCell from 'oro/datagrid/string-cell';
import __ from 'oro/translator';


/**
 * Boolean column cell
 *
 * @extends oro.datagrid.StringCell
 */
export default StringCell.extend({
  /**
   * Render the boolean.
   */
  render: function() {
    var value = this.formatter.fromRaw(this.model.get(this.column.get("name")));
    if (null === value || '' === value) {
      return this;
    }

    var status = (true === value || 'true' === value || '1' === value) ? 'success' : 'important';
    var label = (true === value || 'true' === value || '1' === value) ? 'Yes' : 'No';

    this.$el.empty().html('<span class="AknBadge AknBadge--' + status + '">' + __(label) + '</span>');

    return this;
  }
});

