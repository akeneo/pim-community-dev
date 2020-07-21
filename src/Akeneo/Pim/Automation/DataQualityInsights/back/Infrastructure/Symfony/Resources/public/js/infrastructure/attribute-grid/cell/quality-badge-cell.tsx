const StringCell = require('oro/datagrid/string-cell');
const __ = require('oro/translator');

class QualityBadgeCell extends StringCell {
  render() {
    const attributeEvaluationResult = this.formatter.fromRaw(this.model.get(this.column.get('name')));

    const cssClass = `AknDataQualityInsightsQualityBadge AknDataQualityInsightsQualityBadge--${attributeEvaluationResult.replace('_', '-')}`;

    const content = `<span class="${cssClass}">${__('akeneo_data_quality_insights.attribute_grid.quality.' + attributeEvaluationResult)}</span>`;
    this.$el.empty().html(content);

    return this;
  }
}

export = QualityBadgeCell;
