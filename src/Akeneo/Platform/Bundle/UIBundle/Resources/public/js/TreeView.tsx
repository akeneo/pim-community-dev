import ReactDOM from "react-dom";
import { DependenciesProvider } from "@akeneo-pim-community/legacy-bridge";
import { ThemeProvider } from "styled-components";
import { CategoryTrees } from "@akeneo-pim-community/shared/src/components/CategoryTree/CategoryTrees";
import React from "react";
import {pimTheme} from 'akeneo-design-system';
import { CategoryResponse, parseResponse } from "./CategoryTreeFetcher";
const Router = require('pim/router');

type State = {
  selectedNode: number;
  selectedTree: number;
  includeSub: boolean;
};

class TreeView {
  private domElement: HTMLElement;
  private state: State;

  constructor(
    domElement: HTMLElement,
    initialState: State,
  ) {
    this.domElement = domElement;
    this.state = initialState;

    const init = async () => {
      const url = Router.generate('pim_enrich_product_grid_category_tree_listtree', {
        _format: 'json',
        dataLocale: undefined,
        select_node_id: this.state.selectedNode,
        include_sub: this.state.includeSub ? 1 : 0,
        context: 'view',
      });

      const response = await fetch(url);
      const json: any = await response.json();

      return json.map((tree: any) => {
        return {...tree, selected: tree.selected === 'true'}
      })
    }

    const childrenCallback = async (nodeId: number) => {
      const url = Router.generate('pim_enrich_product_grid_category_tree_children', {
        _format: 'json',
        dataLocale: undefined,
        context: 'view',
        id: nodeId,
        select_node_id: this.state.selectedNode,
        with_items_count: 1,
        include_sub: this.state.includeSub ? 1 : 0,
      });

      const response = await fetch(url);
      const json: CategoryResponse[] = await response.json();
      console.log(json);

      return json.map(json => parseResponse(json, {}));
    }

    const initTree = async (treeId: number, treeLabel: string, treeCode: string) => {
      const url = Router.generate('pim_enrich_product_grid_category_tree_children', {
        _format: 'json',
        dataLocale: undefined,
        context: 'view',
        id: treeId,
        select_node_id: this.state.selectedNode,
        with_items_count: 1,
        include_sub: this.state.includeSub ? 1 : 0,
      });

      const response = await fetch(url);
      const json: CategoryResponse[] = await response.json();
      console.log(json);

      return {
        id: treeId,
        label: treeLabel,
        code: treeCode,
        children: json.map(tree => parseResponse(tree, {})).concat({
          id: -1,
          code: 'unclassified',
          label: 'unclassified products',
          selectable: false,
          children: [],
        }),
        selectable: false,
      }
    }

    const handleClick = (selectedTreeId: number, selectedTreeRootId: number) => {
      console.log(selectedTreeRootId, selectedTreeId);
      this.state.selectedNode = selectedTreeId;
      this.state.selectedTree = selectedTreeRootId;
      this.domElement.dispatchEvent(new Event('tree.updated', {bubbles: true}));
    };

    const handleTreeChange = (treeId: number) => {
      this.state.selectedTree = treeId;
      this.state.selectedNode = 0;
      this.domElement.dispatchEvent(new Event('tree.updated', {bubbles: true}));
    }

    const handleIncludeSubCategories = (value: boolean) => {
      this.state.includeSub = value;
      this.domElement.dispatchEvent(new Event('tree.updated', {bubbles: true}));
    }

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CategoryTrees
            init={init}
            childrenCallback={childrenCallback}
            initTree={initTree}
            onClick={handleClick}
            onTreeChange={handleTreeChange}
            initialIncludeSubCategories={this.state.includeSub}
            onIncludeSubCategoriesChange={handleIncludeSubCategories}
            initialSelectedTreeId={this.state.selectedNode}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.domElement
    );
  }

  public refresh = () => { }


  public getState: () => State = () => {
    return this.state;
  }
}

export = TreeView;
