import ReactDOM from 'react-dom';
import React from 'react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {CategoryTree, CategoryTreeModel, CategoryResponse, parseResponse} from '@akeneo-pim-community/shared';
const Router = require('pim/router');

class TreeAssociate {
  private selectedCategoryCodesByTreeIdInput: HTMLInputElement;
  private listCategoriesRoute: string;
  private childrenRoute: string;
  private productId?: number;
  private productUuid?: string;
  private dataLocale?: string;
  private container: HTMLDivElement;
  private readOnly: boolean;
  private lockedCategoryIds: number[];

  constructor(
    routes: {
      list_categories: string;
      children: string;
    },
    readOnly: boolean = false,
    lockedCategoryIds: number[] = []
  ) {
    this.container = document.getElementById('trees') as HTMLDivElement;
    if (!this.container) {
      return;
    }
    this.selectedCategoryCodesByTreeIdInput = document.getElementById('hidden-tree-input') as HTMLInputElement;
    this.productId = this.container.dataset.id !== '' ? Number(this.container.dataset.id) : undefined;
    this.productUuid = this.container.dataset.uuid !== '' ? this.container.dataset.uuid : undefined;
    this.listCategoriesRoute = routes.list_categories;
    this.childrenRoute = routes.children;
    if (!this.container.dataset.datalocale) {
      throw new Error('The container must have dataLocale data');
    }
    this.dataLocale = this.container.dataset.datalocale;
    this.readOnly = readOnly;
    this.lockedCategoryIds = lockedCategoryIds;

    const selectedTreeId = Number(this.container.dataset.selectedTree);

    this.initTree(selectedTreeId);
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

  private initTree = (treeId: number) => {
    const init: () => Promise<CategoryTreeModel> = async () => {
      if (
        JSON.parse(this.selectedCategoryCodesByTreeIdInput.value)[treeId] &&
        JSON.parse(this.selectedCategoryCodesByTreeIdInput.value)[treeId].length
      ) {
        const params: {[key: string]: any} = {
          uuid: this.productUuid,
          categoryId: treeId,
          _format: 'json',
          context: 'associate',
          dataLocale: this.dataLocale,
          selected: JSON.parse(this.selectedCategoryCodesByTreeIdInput.value)[treeId],
        };
        if (typeof this.productUuid !== 'undefined') {
          params.uuid = this.productUuid;
        }
        if (typeof this.productId !== 'undefined') {
          params.id = this.productId;
        }

        const url = Router.generate(this.listCategoriesRoute, params);

        const response = await fetch(url);
        const json: CategoryResponse[] = await response.json();

        return parseResponse(json[0], {
          readOnly: this.readOnly,
          lockedCategoryIds: this.lockedCategoryIds,
          isRoot: true,
          selectable: true,
        });
      } else {
        const url = Router.generate(this.childrenRoute, {
          _format: 'json',
          context: 'associate',
          dataLocale: this.dataLocale,
          id: treeId,
          include_parent: true,
        });

        const response = await fetch(url);
        const json: CategoryResponse = await response.json();

        return parseResponse(json, {
          readOnly: this.readOnly,
          lockedCategoryIds: this.lockedCategoryIds,
          isRoot: true,
          selectable: true,
        });
      }
    };

    const childrenCallback: (id: number) => Promise<CategoryTreeModel[]> = async id => {
      const response = await fetch(this.getChildrenUrl(id));
      const json: CategoryResponse = await response.json();

      return (json.children || []).map(child =>
        parseResponse(child, {
          readOnly: this.readOnly,
          lockedCategoryIds: this.lockedCategoryIds,
          isRoot: false,
          selectable: true,
        })
      );
    };

    const tree: HTMLDivElement = document.getElementById(`tree-${treeId}`) as HTMLDivElement;

    const handleChange = (value: string, checked: boolean) => {
      const selectedCategoryCodesByTreeId = JSON.parse(this.selectedCategoryCodesByTreeIdInput.value);
      if (!selectedCategoryCodesByTreeId[treeId]) {
        selectedCategoryCodesByTreeId[treeId] = [];
      }
      const index = selectedCategoryCodesByTreeId[treeId].indexOf(value, 0);

      if (checked) {
        if (index <= -1) {
          selectedCategoryCodesByTreeId[treeId].push(value);
          this.selectedCategoryCodesByTreeIdInput.value = JSON.stringify(selectedCategoryCodesByTreeId);
          this.selectedCategoryCodesByTreeIdInput.dispatchEvent(new Event('change', {bubbles: true}));
        }
      } else {
        if (index > -1) {
          selectedCategoryCodesByTreeId[treeId].splice(index, 1);
          this.selectedCategoryCodesByTreeIdInput.value = JSON.stringify(selectedCategoryCodesByTreeId);
          this.selectedCategoryCodesByTreeIdInput.dispatchEvent(new Event('change', {bubbles: true}));
        }
      }
    };

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <CategoryTree onChange={handleChange} childrenCallback={childrenCallback} init={init} />
        </ThemeProvider>
      </DependenciesProvider>,
      tree
    );
  };

  private getChildrenUrl = (id: number) => {
    return Router.generate(this.childrenRoute, {
      _format: 'json',
      context: 'associate',
      dataLocale: this.dataLocale,
      id,
      include_parent: false,
    });
  };
}

export = TreeAssociate;
