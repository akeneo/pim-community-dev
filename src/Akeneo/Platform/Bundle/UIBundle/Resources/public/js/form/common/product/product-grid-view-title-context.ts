import {ProductGridViewTitle} from './ProductGridViewTitle';

const BaseView = require('pimui/js/view/base');
const mediator = require('oro/mediator');

type CurrentView = {
  id: number;
  text: string;
  type: string;
  columns: [];
  filters: string;
};

class ProductGridViewTitleContext extends BaseView {
  private currentView: CurrentView;

  /**
   * {@inheritdoc}
   */
  configure() {
    mediator.on('grid:view:selected', this.onGridViewSelected.bind(this));

    return super.configure();
  }

  onGridViewSelected(view: any) {
    this.currentView = view;
    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): ProductGridViewTitleContext {
    if (!this.currentView) {
      return this;
    }

    this.renderReact(ProductGridViewTitle, {type: this.currentView.type, children: this.currentView.text}, this.el);

    return this;
  }
}

export = ProductGridViewTitleContext;
