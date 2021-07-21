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

type ProjectDetails = {
  dueDateLabel: string;
  dueDate: string;
  completionRatio: number;
  badgeClass: string;
};

class ProductGridViewTitleContext extends BaseView {
  private currentView: CurrentView;
  private projectDetails: ProjectDetails;

  /**
   * {@inheritdoc}
   */
  configure() {
    mediator.on('grid:view:selected', this.onGridViewSelected.bind(this));
    mediator.on('grid:project:selected', this.onGridProjectSelected.bind(this));

    return super.configure();
  }

  onGridViewSelected(view: CurrentView) {
    this.currentView = view;
    this.render();
  }

  onGridProjectSelected(project: CurrentView, projectDetails: ProjectDetails) {
    this.currentView = project;
    this.projectDetails = projectDetails;

    this.render();
  }

  /**
   * {@inheritdoc}
   */
  render(): ProductGridViewTitleContext {
    if (!this.currentView) {
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
