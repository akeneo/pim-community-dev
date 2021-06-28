import {CategoryTreeModel} from '@akeneo-pim-community/shared';

type CategoryResponse = {
  attr: {
    id: string;
    'data-code': string;
  };
  children?: CategoryResponse[];
  data: string;
  /**
   * State is a combination of 'closed', 'leaf', 'open', 'toselect', 'jstree-checked', 'jstree-root' separated by
   * spaces
   * @see src/Akeneo/Pim/Enrichment/Bundle/Twig/CategoryExtension.php
   */
  state: string;
  selectedChildrenCount?: number;
};

const parseResponse: (
  json: CategoryResponse,
  options?: {
    readOnly?: boolean;
    lockedCategoryIds?: number[];
    isRoot?: boolean;
    selectable?: boolean;
    parent?: CategoryTreeModel | null;
  }
) => CategoryTreeModel = (json, options) => {
  const {readOnly, lockedCategoryIds, isRoot, parent, selectable} = {
    readOnly: false,
    lockedCategoryIds: [] as number[],
    isRoot: false,
    selectable: false,
    parent: null,
    ...options,
  };

  const categoryId = Number(json.attr.id.replace(/^node_(\d+)$/, '$1'));
  const categoryTree = {
    id: categoryId,
    code: json.attr['data-code'],
    label: json.data,
    selected: json.state.includes('jstree-checked'),
    readOnly: readOnly || lockedCategoryIds.indexOf(categoryId) >= 0,
    selectable: !isRoot && selectable,
    parent
  };

  const getChildren: () => CategoryTreeModel[] | undefined = () => {
    if (json.state.includes('closed')) {
      return undefined;
    }

    if (json.state.includes('leaf')) {
      return [];
    }

    if (json.children) {
      return json.children.map(child => parseResponse(child, {
        readOnly,
        lockedCategoryIds,
        isRoot: false,
        selectable,
        parent: categoryTree
      }));
    }
    return undefined;
  };

  return {
    ...categoryTree,
    children: getChildren(),
  };
};

export {parseResponse};
export type {CategoryResponse};
