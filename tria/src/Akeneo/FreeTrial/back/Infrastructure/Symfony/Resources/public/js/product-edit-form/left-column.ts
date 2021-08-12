const LeftColumn = require('pim/form/common/column');

class FreeTrialLeftColumn extends LeftColumn {
  isCollapsedByUser = false;

  configure () {
    this.isCollapsedByUser = super.isCollapsed();

    super.getRoot().on('pim_enrich:form:start_compare_translate', this.onStartCompareTranslate, this);
    super.getRoot().on('pim_enrich:form:stop_compare_translate', this.onStopCompareTranslate, this);

    return super.configure();
  }

  onStartCompareTranslate() {
    // Collapse the left column if it's open
    if (!super.isCollapsed()) {
      super.setCollapsed(true);
    }
  }

  onStopCompareTranslate() {
    // Re-open the left column, unless the user had collapsed it before starting compare/translate
    if (!this.isCollapsedByUser && super.isCollapsed()) {
      super.setCollapsed(false);
    }
  }

  toggleColumn() {
    super.toggleColumn();
    this.isCollapsedByUser = super.isCollapsed();
  }
}

export = FreeTrialLeftColumn;
