import ReactDOM from 'react-dom';
import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import { ThemeProvider } from "styled-components";
import { pimTheme } from 'akeneo-design-system';
import { CategoryTree } from "@akeneo-pim-community/shared/src/components/CategoryTree/CategoryTree";
const Router = require('pim/router');

class TreeAssociate {
  private locked = false;
  private container: HTMLDivElement;
  private productId: number;
  private selectedCategoryCodesByTreeIdInput: HTMLInputElement;
  private listCategoriesRoute: string;
  private childrenRoute: string;
  private dataLocale: string;
  private lockedCategoryIds: number[];

  constructor(
    elementId: string,
    hiddenCategoryId: string,
    routes: {
      list_categories: string,
      children: string,
    },
    lockedCategoryIds: number[] = []
  ) {
    const elementIdWithoutSharp = elementId.replace(/^#(.*)$/, '$1');
    this.container = document.getElementById(elementIdWithoutSharp) as HTMLDivElement;
    const hiddenCategoryIdWithoutSharp = hiddenCategoryId.replace(/^#(.*)$/, '$1');
    this.selectedCategoryCodesByTreeIdInput = document.getElementById(hiddenCategoryIdWithoutSharp) as HTMLInputElement;
    if (null === this.container) {
      return;
    }
    if (null === this.selectedCategoryCodesByTreeIdInput) {
      throw new Error(`The hidden field ${hiddenCategoryIdWithoutSharp} was not found`);
    }

    this.listCategoriesRoute = routes.list_categories;
    this.childrenRoute = routes.children;
    const selectedTree = this.container.dataset.selectedTree;
    const dataLocale = this.container.dataset.datalocale
    if (!dataLocale) {
      throw new Error(`The container should provide a dataLocale`);
    }
    this.dataLocale = dataLocale;
    this.productId = Number(this.container.dataset.id);
    this.lockedCategoryIds = lockedCategoryIds;

    if (selectedTree) {
      this.switchTree(selectedTree);
    }
  }

  public switchTree(treeIdStr: string) {
    const treeId: number = Number(treeIdStr);
    const subs = this.container.children;
    Array.from(subs).forEach((sub: HTMLDivElement) => {
      if (Number(sub.dataset.treeId) === treeId) {
        sub.hidden = false;
      } else {
        sub.hidden = true;
      }
    });
    const tree: HTMLDivElement = document.getElementById(`tree-${treeId}`) as HTMLDivElement;
    if (!tree.hasChildNodes()) {
      this.initTree(treeId);
    }
  }

  private initTree(treeId: number) {
    const tree: HTMLDivElement = document.getElementById(`tree-${treeId}`) as HTMLDivElement;

    const handleSelect = (value: string) => {
      const selectedCategoryCodesByTreeId = JSON.parse(this.selectedCategoryCodesByTreeIdInput.value);
      if (!selectedCategoryCodesByTreeId[treeId]) {
        selectedCategoryCodesByTreeId[treeId] = [];
      }
      const index = selectedCategoryCodesByTreeId[treeId].indexOf(value, 0);
      if (index <= -1) {
        selectedCategoryCodesByTreeId[treeId].push(value);
        this.selectedCategoryCodesByTreeIdInput.value = JSON.stringify(selectedCategoryCodesByTreeId);
        this.selectedCategoryCodesByTreeIdInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    const handleUnselect = (value: string) => {
      const selectedCategoryCodesByTreeId = JSON.parse(this.selectedCategoryCodesByTreeIdInput.value);
      if (!selectedCategoryCodesByTreeId[treeId]) {
        selectedCategoryCodesByTreeId[treeId] = [];
      }
      const index = selectedCategoryCodesByTreeId[treeId].indexOf(value, 0);
      if (index > -1) {
        selectedCategoryCodesByTreeId[treeId].splice(index, 1);
        this.selectedCategoryCodesByTreeIdInput.value = JSON.stringify(selectedCategoryCodesByTreeId);
        this.selectedCategoryCodesByTreeIdInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    const initRoute =
      JSON.parse(this.selectedCategoryCodesByTreeIdInput.value)[treeId] &&
      JSON.parse(this.selectedCategoryCodesByTreeIdInput.value)[treeId].length ?
      Router.generate(this.listCategoriesRoute, {
        id: this.productId,
        categoryId: treeId,
        _format: 'json',
        context: 'associate',
        dataLocale: this.dataLocale,
      }) :
      Router.generate(this.childrenRoute, {
        _format: 'json',
        context: 'associate',
        dataLocale: this.dataLocale,
        id: treeId,
        include_parent: true,
      });

    const childrenRoute: (value: string) => string = (value) => {
      return Router.generate('pim_enrich_categorytree_children', {
        _format: 'json',
        context: 'associate',
        dataLocale: 'en_US',
        code: value,
        include_parent: false,
      });
    }

    ReactDOM.render(<DependenciesProvider>
      <ThemeProvider theme={pimTheme}>
        <CategoryTree
          onSelect={handleSelect}
          onUnselect={handleUnselect}
          initRoute={initRoute}
          childrenRoute={childrenRoute}
          selectable={!this.locked}
          lockedCategoryIds={this.lockedCategoryIds}
        />
      </ThemeProvider>
    </DependenciesProvider>, tree);
  }
}

export = TreeAssociate;
