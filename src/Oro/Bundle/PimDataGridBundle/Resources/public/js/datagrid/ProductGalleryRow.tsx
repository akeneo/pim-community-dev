import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {Badge, Card, pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ViewOptions} from 'backbone';

const BaseRow = require('oro/datagrid/row');
const MediaUrlGenerator = require('pim/media-url-generator');
const Router = require('pim/router');

class ProductGalleryRow extends BaseRow {
  reactRef: Element | null;
  selected: boolean;

  constructor(options?: ViewOptions) {
    super({
      ...options,
      tagName: 'tr',
      className: '',
    });

    this.selected = false;
    this.reactRef = null;
  }

  initialize(options: object) {
    super.initialize(options);

    // Remove the parent listener for the 'backgrid:selected' event
    this.stopListening(this.model, 'backgrid:selected');

    this.listenTo(this.model, 'backgrid:select', (_: any, isSelected: boolean) => {
      this.model.trigger('backgrid:selected', this.model, isSelected);
    });

    this.listenTo(this.model, 'backgrid:selected', (_: any, isSelected: boolean) => {
      this.updateSelection(isSelected);
    });
  }

  render() {
    const label = this.model.get('label');
    const imagePath = this.getImagePath();
    const badgeText = this.getBadgeText();
    const badgeLevel = this.getBadgeLevel();

    const followProduct = () => {
      const technicalId = this.model.get('technical_id');
      const route = this.isProductModel() ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit';
      Router.redirectToRoute(route, {
        id: technicalId,
      });
    };

    const notifySelection = (isSelected: boolean) => {
      this.model.trigger('backgrid:selected', this.model, isSelected);
    };

    const productCard = (
      <Card
        fit="cover"
        src={imagePath}
        onClick={followProduct}
        isSelected={this.selected}
        onSelect={notifySelection}
        stacked={this.isProductModel()}
        // @ts-ignore
        as="td"
      >
        <Card.BadgeContainer>
          <Badge level={badgeLevel}>{badgeText}</Badge>
        </Card.BadgeContainer>
        {label}
      </Card>
    );

    this.renderReactElement(productCard, this.el);

    return this;
  }

  remove() {
    this.unmountReact();
    super.remove();

    return this;
  }

  onClick(event: MouseEvent) {
    // prevent rowClickAction
    event.preventDefault();
  }

  renderReactElement(component: React.ReactElement, container: Element) {
    this.reactRef = container;

    ReactDOM.render(
      React.createElement(ThemeProvider, {theme: pimTheme}, React.createElement(DependenciesProvider, null, component)),
      this.reactRef
    );
  }

  unmountReact() {
    if (null !== this.reactRef) {
      ReactDOM.unmountComponentAtNode(this.reactRef);
      this.reactRef = null;
    }
  }

  updateSelection(isSelected: boolean) {
    this.selected = isSelected;
    this.render();
  }

  isProductModel() {
    return this.model.get('document_type') === 'product_model';
  }

  getImagePath() {
    const image = this.model.get('image');

    if (undefined === image || null === image) {
      return '/media/show/undefined/preview';
    }

    return MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail');
  }

  getBadgeText() {
    if (this.isProductModel()) {
      const complete = this.model.get('complete_variant_products').complete;
      const total = this.model.get('complete_variant_products').total;

      return `${complete} / ${total}`;
    }

    const completeness = this.model.get('completeness');
    if (null !== completeness) {
      return `${completeness}%`;
    }

    return null;
  }

  getBadgeLevel(): 'primary' | 'danger' | 'warning' | undefined {
    if (this.isProductModel()) {
      const complete = this.model.get('complete_variant_products').complete;
      const total = this.model.get('complete_variant_products').total;
      if (complete === total) {
        return 'primary';
      }

      if (complete === 0) {
        return 'danger';
      }

      return 'warning';
    }

    const completeness = this.model.get('completeness');
    if (null !== completeness) {
      if (completeness <= 0) {
        return 'danger';
      }
      if (completeness >= 100) {
        return 'primary';
      }

      return 'warning';
    }
    return undefined;
  }
}

export = ProductGalleryRow;
