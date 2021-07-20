import {ProductGridContext} from './ProductGridContext';

const BaseView = require('pimui/js/view/base');
const mediator = require('oro/mediator');

type CurrentView = {
  id: number;
  text: string;
  type: string;
  columns: [];
  filters: string;
};

class ProductGridTitleContext extends BaseView {
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
  render(): ProductGridTitleContext {
    if (!this.currentView) {
      return this;
    }

    this.renderReact(ProductGridContext, {type: this.currentView.type, children: this.currentView.text}, this.el);

    return this;
  }
}

export = ProductGridTitleContext;
