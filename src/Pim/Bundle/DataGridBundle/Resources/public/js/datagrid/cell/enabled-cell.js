/* global define */
import StringCell from 'oro/datagrid/string-cell';
import __ from 'oro/translator';


/**
 * Enabled column cell
 *
 * @extends oro.datagrid.StringCell
 */
export default StringCell.extend({
  /**
   * Render the field enabled.
   */
  render: function() {
    var value = this.formatter.fromRaw(this.model.get(this.column.get("name")));
    var enabled = true === value ? 'enabled' : 'disabled';

    this.$el.empty().html('<div class="AknBadge AknBadge--round AknBadge--' + enabled + ' status-' + enabled + '">' +
      '<i class="AknBadge-icon icon-status-' + enabled + ' icon-circle"></i>' + __(enabled) + '</div>');

    return this;
  }
});

