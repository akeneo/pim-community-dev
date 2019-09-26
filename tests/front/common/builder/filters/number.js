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
      choices: [
        { label:"=", value: 3, data: 3, attr: [] },
        { label:">=", value: 1, data: 1, attr: [] },
        { label:">", value: 2, data: 2, attr: [] },
        { label:"<=", value: 4, data: 4, attr: [] },
        { label:"<", value: 5, data: 5, attr: [] },
        { label:"is empty", value: "empty", data:"empty", attr: [] },
        { label:"is not empty", value: "not empty", data:"not empty", attr: [] }
      ],
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
    this.filter.name = name

    return this;
  }

  withLabel(label) {
    this.filter.label = label

    return this;
  }

  withChoices(choices) {
    this.filter.choices = choices

    return this;
  }

  withEnabled(enabled) {
    this.filter.enabled = enabled

    return this;
  }

  withType(type) {
    this.filter.type = type

    return this;
  }

  withOrder(order) {
    this.filter.order = order

    return this;
  }

  withGroup(group) {
    this.filter.group = group

    return this;
  }

  withGroupOrder(groupOrder) {
    this.filter.groupOrder = groupOrder

    return this;
  }

  withFormatterOptions(formatterOptions) {
    this.filter.formatterOptions = formatterOptions

    return this;
  }

  build() {
    return this.filter;
  }
}

module.exports = NumberFilterBuilder;