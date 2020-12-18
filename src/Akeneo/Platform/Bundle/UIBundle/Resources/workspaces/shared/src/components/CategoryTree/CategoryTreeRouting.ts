import { TreeModel } from "./CategoryTreeModel";

export type CategoryResponse = {
  attr: {
    id: string;
    'data-code': string
  },
  children?: CategoryResponse[],
  data: string;
  state: string; // 'closed jstree-root' | 'leaf' | 'closed' | 'leaf toselect jstree-checked';
  selectedChildrenCount?: number;
}

export const parseResponse: (json: CategoryResponse) => TreeModel = (json) => {
  const getChildren: () => TreeModel[] | undefined = () => {
    if (json.state.includes('closed')) {
      return undefined;
    }
    if (json.state.includes('leaf')) {
      return [];
    }
    if (json.children) {
      return json.children.map(child => parseResponse(child));
    }
    return undefined;
  }

  return {
    value: json.attr['data-code'],
    label: json.data,
    children: getChildren(),
    selected: json.state.includes('jstree-checked'),
  }
}
