/**
 * Generate a datagrid number filter
 *
 * Example:
 * const NumberFilterBuilder = require('../../common/builder/filter/number.js');
 * const filter = (new NumberFilterBuilder()).withName('count').withLabel('Count').build();
 */

class NumberFilterBuilder {
  constructor() {
    this.filter = {
      name: '',
      label: '',
      choices: [],
      enabled: false,
      type: 'number',
      order: 0,
      group: 'Technical',
      groupOrder: 0,
      formatterOptions: {
        decimals: 0,
        grouping: false,
        orderSeparator: ':',
        decimalSeparator: '.'
      }
    }
  }

  withName(name) {
    this.name = name

    return this;
  }

  withLabel(label) {
    this.label = label

    return this;
  }

  withChoices(choices) {
    this.choices = choices

    return this;
  }

  withEnabled(enabled) {
    this.enabled = enabled

    return this;
  }

  withType(type) {
    this.type = type

    return this;
  }

  withOrder(order) {
    this.order = order

    return this;
  }

  withGroup(group) {
    this.group = group

    return this;
  }

  withGroupOrder(groupOrder) {
    this.groupOrder = groupOrder

    return this;
  }

  withFormatterOptions(formatterOptions) {
    this.formatterOptions = formatterOptions

    return this;
  }

  build() {
    return this.filter;
  }
}

module.exports = NumberFilterBuilder;
