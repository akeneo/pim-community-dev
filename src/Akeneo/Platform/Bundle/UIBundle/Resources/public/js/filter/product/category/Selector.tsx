import React from 'react';
import ReactDOM from 'react-dom';
import {DependenciesProvider} from '@akeneo-pim-community/shared';
import {Channel, Category, CategoryTree, CategoryTreeModel, CategoryValue} from '@akeneo-pim-community/shared';
import styled, {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Tree} from 'akeneo-design-system/lib';
import {CategoryResponse, parseResponse} from '../../../CategoryTreeFetcher';
const UserContext = require('pim/user-context');
const __ = require('oro/translator');
const Router = require('pim/router');
const FetcherRegistry = require('pim/fetcher-registry');
import BaseView = require('pimui/js/view/base');

const CategoryTreeContainer = styled.div`
  max-height: calc(100vh - 240px);
  overflow: hidden auto;
`;

type CategorySelectorWithAllProductsProps = {
  channelCode: string;
  onChange: (value: string[]) => void;
  initialCategoryCodes: string[];
};

const CategorySelectorWithAllProducts: React.FC<CategorySelectorWithAllProductsProps> = ({
  channelCode,
  onChange,
  initialCategoryCodes,
}) => {
  const [categoryCodes, setCategoryCodes] = React.useState<string[]>(initialCategoryCodes);

  const isCategorySelected: (category: CategoryValue) => boolean = category => {
    return categoryCodes.includes(category.code);
  };

  const handleChange = (value: string, checked: boolean) => {
    const index = categoryCodes.indexOf(value, 0);
    if (checked) {
      if (index <= -1) {
        categoryCodes.push(value);
        setCategoryCodes([...categoryCodes]);
        onChange(categoryCodes);
      }
    } else {
      if (index > -1) {
        categoryCodes.splice(index, 1);
        setCategoryCodes([...categoryCodes]);
        onChange(categoryCodes);
      }
    }
  };

  const getChildrenUrl = (id: number) => {
    return Router.generate('pim_enrich_categorytree_children', {
      _format: 'json',
      id,
    });
  };

  const childrenCallback: (id: number) => Promise<CategoryTreeModel[]> = async id => {
    const response = await fetch(getChildrenUrl(id));
    const json: CategoryResponse[] = await response.json();

    return json.map(child =>
      parseResponse(child, {
        selectable: true,
      })
    );
  };

  const init = async () => {
    await FetcherRegistry.initialize();
    const channel: Channel = await FetcherRegistry.getFetcher('channel').fetch(channelCode);
    const category: Category = await FetcherRegistry.getFetcher('category').fetch(channel.category_tree);
    const response = await fetch(getChildrenUrl(category.id));
    const json: CategoryResponse[] = await response.json();

    return {
      id: category.id,
      code: category.code,
      label: category.labels[UserContext.get('catalogLocale')] || `[${category.code}]`,
      selectable: false,
      children: json.map(child =>
        parseResponse(child, {
          selectable: true,
        })
      ),
    };
  };

  return (
    <CategoryTreeContainer>
      <CategoryTree
        onChange={handleChange}
        childrenCallback={childrenCallback}
        init={init}
        isCategorySelected={isCategorySelected}
      />
      <Tree
        value={'all'}
        label={__('jstree.all')}
        isLeaf={true}
        selectable={true}
        selected={categoryCodes.length === 0}
        onChange={(_value, checked) => {
          if (checked) {
            setCategoryCodes([]);
            onChange([]);
          }
        }}
      />
    </CategoryTreeContainer>
  );
};

type SelectorConfig = {
  el: any;
  attributes: {
    channel: string;
    categories: string[];
  };
};

class Selector extends BaseView {
  private channelCode: string;
  private categoryCodes: string[];

  constructor(options: SelectorConfig) {
    super(options);
    this.channelCode = options.attributes.channel;
    this.categoryCodes = options.attributes.categories || [];
  }

  render() {
    const handleChange = (categoryCodes: string[]) => {
      this.categoryCodes = categoryCodes;
    };

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CategorySelectorWithAllProducts
            channelCode={this.channelCode}
            initialCategoryCodes={this.categoryCodes}
            onChange={handleChange}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.$el[0]
    );

    return this;
  }

  public getCategoryCodes: () => string[] = () => {
    return this.categoryCodes;
  };
}

export = Selector;
