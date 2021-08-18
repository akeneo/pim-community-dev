const BaseColumn = require('pim/form/common/column');

class ProductEditFormSubNavigation extends BaseColumn {
  isCollapsedByUser = false;

  configure() {
    this.isCollapsedByUser = this.isCollapsed();

    this.getRoot().on('pim_enrich:form:start_copy', this.onStartCompareTranslate, this);
    this.getRoot().on('pim_enrich:form:stop_copy', this.onStopCompareTranslate, this);

    return super.configure();
  }

  onStartCompareTranslate() {
    // Collapse the left column if it's open
    if (!this.isCollapsed()) {
      this.setCollapsed(true);
    }
  }

  onStopCompareTranslate() {
    // Re-open the left column, unless the user had collapsed it before starting compare/translate
    if (!this.isCollapsedByUser && this.isCollapsed()) {
      this.setCollapsed(false);
    }
  }

  toggleColumn() {
    super.toggleColumn();
    this.isCollapsedByUser = this.isCollapsed();
  }
}

export = ProductEditFormSubNavigation;
