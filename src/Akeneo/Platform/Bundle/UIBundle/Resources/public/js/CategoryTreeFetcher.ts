import {CategoryTreeModel} from '@akeneo-pim-community/shared/src/components/CategoryTree/CategoryTree';

type CategoryResponse = {
  attr: {
    id: string;
    'data-code': string;
  };
  children?: CategoryResponse[];
  data: string;
  state: string; // 'closed jstree-root' | 'leaf' | 'closed' | 'leaf toselect jstree-checked';
  selectedChildrenCount?: number;
};

const parseResponse: (
  json: CategoryResponse,
  options?: {
    readOnly?: boolean;
    lockedCategoryIds?: number[];
    isRoot?: boolean;
    selectable?: boolean;
  }
) => CategoryTreeModel = (json, options) => {
  const {readOnly, lockedCategoryIds, isRoot, selectable} = {
    readOnly: false,
    lockedCategoryIds: [] as number[],
    isRoot: false,
    selectable: false,
    ...options,
  };

  const getChildren: () => CategoryTreeModel[] | undefined = () => {
    if (json.state.includes('closed')) {
      return undefined;
    }
    if (json.state.includes('leaf')) {
      return [];
    }
    if (json.children) {
      return json.children.map(child => parseResponse(child, {readOnly, lockedCategoryIds, isRoot: false, selectable}));
    }
    return undefined;
  };

  const categoryId = Number(json.attr.id.replace(/^node_(\d+)$/, '$1'));

  return {
    id: categoryId,
    code: json.attr['data-code'],
    label: json.data,
    children: getChildren(),
    selected: json.state.includes('jstree-checked'),
    readOnly: readOnly || lockedCategoryIds.indexOf(categoryId) >= 0,
    selectable: !isRoot && selectable,
  };
};

export {CategoryResponse, parseResponse};
