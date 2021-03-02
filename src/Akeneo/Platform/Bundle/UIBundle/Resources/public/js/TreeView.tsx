import ReactDOM from 'react-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {CategoryTrees} from '@akeneo-pim-community/shared';
import React from 'react';
import {pimTheme} from 'akeneo-design-system';
import {CategoryResponse, parseResponse} from './CategoryTreeFetcher';
const Router = require('pim/router');
const __ = require('oro/translator');

type State = {
  selectedNode: number;
  selectedTree: number;
  includeSub: boolean;
};

class TreeView {
  private domElement: HTMLElement;
  private state: State;
  private onChange: (treeLabel: string, categoryLabel?: string) => void;
  private listTreeRoute: string;
  private childrenRoute: string;

  constructor(
    domElement: HTMLElement,
    initialState: State,
    routes: {
      listTree: string;
      children: string;
    },
    onChange: (treeLabel: string, categoryLabel: string) => void
  ) {
    this.domElement = domElement;
    this.state = initialState;
    this.onChange = onChange;
    this.listTreeRoute = routes.listTree;
    this.childrenRoute = routes.children;

    this.initTreeView();
  }

  private initTreeView = () => {
    const init = async () => {
      const url = Router.generate(this.listTreeRoute, {
        _format: 'json',
        dataLocale: undefined,
        select_node_id: this.state.selectedNode,
        include_sub: this.state.includeSub ? 1 : 0,
        context: 'view',
      });

      const response = await fetch(url);
      const json: any = await response.json();

      return json.map((tree: any) => {
        return {...tree, selected: tree.selected === 'true'};
      });
    };

    const childrenCallback = async (categoryId: number) => {
      const url = Router.generate(this.childrenRoute, {
        _format: 'json',
        dataLocale: undefined,
        context: 'view',
        id: categoryId,
        select_node_id: this.state.selectedNode,
        with_items_count: 1,
        include_sub: this.state.includeSub ? 1 : 0,
      });

      const response = await fetch(url);
      const json: CategoryResponse[] = await response.json();

      return json.map(json => parseResponse(json, {}));
    };

    const initTree = async (treeId: number, treeLabel: string, treeCode: string, includeSub: boolean) => {
      const url = Router.generate(this.childrenRoute, {
        _format: 'json',
        dataLocale: undefined,
        context: 'view',
        id: treeId,
        select_node_id: this.state.selectedNode,
        with_items_count: 1,
        include_sub: includeSub ? 1 : 0,
      });

      const response = await fetch(url);
      const json: CategoryResponse[] = await response.json();

      return {
        id: treeId,
        label: treeLabel,
        code: treeCode,
        children: json
          .map(tree => parseResponse(tree, {}))
          .concat({
            id: -1,
            code: 'unclassified',
            label: __('jstree.unclassified'),
            selectable: false,
            children: [],
          }),
        selectable: false,
      };
    };

    const handleCategoryClick = (categoryId: number, treeId: number, categoryLabel: string, treeLabel: string) => {
      this.state.selectedNode = categoryId;
      this.state.selectedTree = treeId;
      this.domElement.dispatchEvent(new Event('tree.updated', {bubbles: true}));
      this.onChange(treeLabel, categoryLabel);
    };

    const handleTreeChange = (treeId: number, treeLabel: string) => {
      this.state.selectedTree = treeId;
      this.state.selectedNode = treeId;
      this.domElement.dispatchEvent(new Event('tree.updated', {bubbles: true}));
      this.onChange(treeLabel);
    };

    const handleIncludeSubCategoriesChange = (includeSubCategories: boolean) => {
      this.state.includeSub = includeSubCategories;
      this.domElement.dispatchEvent(new Event('tree.updated', {bubbles: true}));
    };

    const initCallback = (treeLabel: string, categoryLabel: string) => {
      this.onChange(treeLabel, categoryLabel);
    };

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CategoryTrees
            childrenCallback={childrenCallback}
            init={init}
            initTree={initTree}
            initCallback={initCallback}
            initialIncludeSubCategories={this.state.includeSub}
            initialSelectedTreeId={this.state.selectedNode}
            onCategoryClick={handleCategoryClick}
            onTreeChange={handleTreeChange}
            onIncludeSubCategoriesChange={handleIncludeSubCategoriesChange}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.domElement
    );
  };

  public refresh = () => {
    ReactDOM.unmountComponentAtNode(this.domElement);
    this.initTreeView();
  };

  public getState: () => State = () => {
    return this.state;
  };
}

export = TreeView;
