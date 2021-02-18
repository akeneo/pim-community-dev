import React from 'react';
import ReactDOM from 'react-dom';
import styled, {ThemeProvider} from 'styled-components';
import {Badge, Card, pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {Model, ViewOptions} from 'backbone';

const BaseRow = require('oro/datagrid/row');
const MediaUrlGenerator = require('pim/media-url-generator');
const Router = require('pim/router');

const BadgeContainer = styled.div`
  position: absolute;
  z-index: 5;
  top: 10px;
  right: 10px;
`;

class ProductGalleryRow<TModel extends Model> extends BaseRow {
  public constructor(options?: ViewOptions<TModel>) {
    super({
      ...options,
      tagName: 'div',
      className: '',
    });

    this.selected = false;
  }

  public render() {
    const label = this.model.get('label');
    const imagePath = this.getImagePath();
    const badgeText = this.getBadgeText();
    const badgeLevel = this.getBadgeLevel();
    const selection = {selected: false};

    // Initialize the selection state
    this.model.trigger('backgrid:isSelected', this.model, selection);

    const followProduct = () => {
      const technicalId = this.model.get('technical_id');
      const route = this.isProductModel() ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit';
      Router.redirectToRoute(route, {
        id: technicalId
      });
    };

    const selectProduct = (isSelected: boolean) => {
      this.model.trigger('backgrid:selected', this.model, isSelected);

      // @fixme: the rerender cause the following warning in the console  "Warning: render(...): It looks like the React-rendered content of this container was removed without using React. This is not supported and will cause errors. Instead, call ReactDOM.unmountComponentAtNode to empty a container"
      this.render();
    };

    const card = (
      <Card fit="cover" src={imagePath} onClick={followProduct} isSelected={selection.selected} onSelect={selectProduct}>
        <BadgeContainer>
          <Badge level={badgeLevel}>{badgeText}</Badge>
        </BadgeContainer>
        {label}
      </Card>
    );

    this.renderReactElement(card, this.el);

    return this;
  }

  private renderReactElement(reactElement: React.ReactElement, container: Element) {
    this.reactRef = container;
    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(DependenciesProvider, null, reactElement)
      ),
      this.reactRef
    );
  }

  private isProductModel() {
    return this.model.get('document_type') === 'product_model';
  }

  private getImagePath() {
    const image = this.model.get('image');

    if (undefined === image || null === image) {
      return '/media/show/undefined/preview';
    }

    return MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail');
  }

  private getBadgeText() {
    if (this.isProductModel()) {
      const complete = this.model.get('complete_variant_products').complete;
      const total = this.model.get('complete_variant_products').total;

      return complete + ' / ' + total;
    }

    const completeness = this.model.get('completeness');
    if (null !== completeness) {
      return completeness + '%';
    }

    return null;
  }

  private getBadgeLevel(): 'primary' | 'danger' | 'warning' | undefined {
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
