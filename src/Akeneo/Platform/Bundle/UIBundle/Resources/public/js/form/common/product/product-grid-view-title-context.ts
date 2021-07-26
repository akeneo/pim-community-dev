import {ProductGridViewTitle} from '../../../grid/ProductGridViewTitle';

const BaseView = require('pimui/js/view/base');
const mediator = require('oro/mediator');

type CurrentView = {
  text: string;
  type: string;
};

type ProjectDetails = {
  dueDateLabel: string;
  dueDate: string;
  completionRatio: number;
};

class ProductGridViewTitleContext extends BaseView {
  private currentView: CurrentView;
  private projectDetails: ProjectDetails | null;

  /**
   * {@inheritdoc}
   */
  configure() {
    mediator.on('grid:view:selected', this.onGridViewSelected.bind(this));
    mediator.on('grid:project:selected', this.onGridViewSelected.bind(this));

    return super.configure();
  }

  onGridViewSelected(view: CurrentView, projectDetails: ProjectDetails | null = null) {
    this.currentView = view;
    this.projectDetails = projectDetails;

    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): ProductGridViewTitleContext {
    if (!this.currentView || (this.currentView.type === 'project' && !this.projectDetails)) {
      return this;
    }

    this.renderReact(
      ProductGridViewTitle,
      {type: this.currentView.type, projectDetails: this.projectDetails, children: this.currentView.text},
      this.el
    );

    return this;
  }
}

export = ProductGridViewTitleContext;
