import ReactDOM from "react-dom";
import { DependenciesProvider } from "@akeneo-pim-community/legacy-bridge";
import { ThemeProvider } from "styled-components";
import React from "react";
import {pimTheme} from 'akeneo-design-system';
import { CategoryTreeWithTreeSelector } from "@akeneo-pim-community/shared/src/components/CategoryTree/CategoryTreeWithTreeSelector";
const Router = require('pim/router');

class TreeView {
  private dataLocale?: string;
  private relatedEntity : string; // 'product'
  private selectedNode: number;
  private selectedTree: number;
  private includeSub: boolean;
  private baseRoute: string;
  private onClick: any;

  constructor(
    element: HTMLElement,
    state: any,
    baseRoute: string,
    onClick: (treeState: any) => void,
  ) {
    this.dataLocale = element.dataset.dataLocale;
    const relatedEntity = element.dataset.relatedentity;
    if (typeof(relatedEntity) === 'undefined') {
      throw new Error('TreeView should have relatedentity prop');
    }
    this.relatedEntity = relatedEntity;
    this.baseRoute = baseRoute;
    this.selectedNode = state.selectedNode;
    this.selectedTree = state.selectedTree;
    this.includeSub = state.includeSub;
    this.onClick = onClick;

    const getTreeUrl = () => {
      return Router.generate(this.getRoute('listtree'), {
        _format: 'json',
        dataLocale: this.dataLocale,
        select_node_id: this.getActiveNode(true),
        include_sub: +this.includeSub,
        context: 'view',
      });
    };

    fetch(getTreeUrl()).then(response => {
      response.json().then((json: any[]) => {
        ReactDOM.render(
          <DependenciesProvider>
            <ThemeProvider theme={pimTheme}>
              <CategoryTreeWithTreeSelector
                trees={json}
                childrenUrl={this.getChildrenUrl}
                onClick={(_value) => {
                  this.selectedNode = 10;
                  this.onClick(this.getState());
                }}
              />
            </ThemeProvider>
          </DependenciesProvider>,
          element
        );
      });
    });
  }

  private getChildrenUrl: (code: string, includeParent?: boolean) => string = (code, includeParent = false) =>
    Router.generate(this.getRoute('children'), {
      _format: 'json',
      dataLocale: this.dataLocale,
      context: 'view',
      code,
      select_node_id: this.getActiveNode(true),
      switch_items_count: 1,
      //include_sub: +this.includeSub,
      include_parent: includeParent,
    });

  private getRoute: (routeName: string) => string = (routeName) =>
    `${this.baseRoute}_${routeName}`;

  private getActiveNode = (skipVirtual: boolean) => {
    if (skipVirtual) {
      return this.selectedNode > 0 ? this.selectedNode : this.selectedTree;
    }

    return this.selectedNode !== 0 ? this.selectedNode : this.selectedTree;
  };

  public getState = () => {
    return {
      selectedNode: this.selectedNode,
      selectedTree: this.selectedTree,
      includeSub: this.includeSub,
    };
  }
}

export = TreeView;
