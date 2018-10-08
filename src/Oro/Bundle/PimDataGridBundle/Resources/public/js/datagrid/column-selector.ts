import BaseView = require('pimenrich/js/view/base');
// import * as _ from 'underscore';

class ColumnSelector extends BaseView {
  public config: any;

  constructor(options: {config: any}) {
    super({...options, ...{className: 'filter-box'}});

    this.config = {...this.config, ...options.config};
  }

  configure() {
    return BaseView.prototype.configure.apply(this, arguments);
  }

  render() {
      this.$el.html('<button>go</button>');

      return this;
  }
}

export = ColumnSelector;
