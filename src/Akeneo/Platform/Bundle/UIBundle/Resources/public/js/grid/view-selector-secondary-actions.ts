import {EventsHash} from 'backbone';

const BaseView = require('pim/form/common/secondary-actions');

class ViewSelectorSecondaryActions extends BaseView {
  /*
    @see Oro/datafilter/filter/abstract-filter.js::_updateCriteriaSelectorPosition()
    The current css position cannot be used in a column, we have to manually set it as fixed on open
   */
  public events(): EventsHash {
    return {
      'click .dropdown-button': () => {
        this.$el.find('.AknDropdown-menu').css({
          position: 'fixed',
          left: this.$el.offset().left,
          minWidth: 'auto',
          right: 'auto',
          top: this.$el.offset().top,
        });
      },
    };
  }
}

export = ViewSelectorSecondaryActions;
