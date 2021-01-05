import { CategoryTree } from "@akeneo-pim-community/shared/src/components/CategoryTree/CategoryTree";
import React from "react";
import { TreeModel } from "@akeneo-pim-community/shared/src/components/CategoryTree/CategoryTreeModel";
import { parseResponse } from "@akeneo-pim-community/shared/src/components/CategoryTree/CategoryTreeRouting";
import { Tree } from 'akeneo-design-system';
//const Router = require('pim/router');

type TreeToto = {
  id: number,
  code: string,
  label: string,
  selected: boolean,
}

type CategoryTreeWithTreeSelectorProps = {
  trees: TreeToto[];
  childrenUrl: (code: string, includeParent?: boolean) => string;
  onClick?: (value: string) => void;
}

const CategoryTreeWithTreeSelector: React.FC<CategoryTreeWithTreeSelectorProps> = ({
  trees,
  childrenUrl,
  onClick
}) => {
  if (trees.length === 0) {
    return <div/>;
  }

  const [currentTreeCode, setCurrentTreeCode] = React.useState<string>(
    trees.find(tree => tree.selected)?.code || trees[0].code
  );

  const [treeMap, setTreeMap] = React.useState<{[code: string]: TreeModel}>({});

  const selectTree = (code: string) => {
    setCurrentTreeCode(code);
    initTree(code);
  }

  const initTree = (code: string) => {
    if (typeof (treeMap[code]) === 'undefined') {
      const currentTreeData = trees.find(tree => tree.code === code) as TreeToto;
      fetch(childrenUrl(currentTreeData.code)).then(response => {
        response.json().then((json: any) => {
          const t = {
            value: currentTreeData.code,
            label: currentTreeData.label,
            children: (json as Array<any>).map(tree => parseResponse(tree, false, []))
          }
          setTreeMap({ ...treeMap, [code]: t});
        });
      });
    }
  }

  /*
  const childrenRoute: (value: string) => string = value => {
    return Router.generate('pim_enrich_categorytree_children', {
      _format: 'json',
      context: 'view',
      dataLocale: 'en_US',
      code: value,
      include_parent: false,
    });
  };*/

  React.useEffect(() => initTree(currentTreeCode), []);

  return <>
    {trees.map((tree) => {
      return <button key={tree.code} onClick={() => selectTree(tree.code)}>{tree.label}</button>
    })}
    {typeof (treeMap[currentTreeCode]) === 'undefined' ?
      <Tree isLoading={true} value={''} label={''}/> :
      <CategoryTree
        onClick={onClick}
        initialTree={treeMap[currentTreeCode]}
        childrenRoute={childrenUrl}
      />
    }
  </>
}

export {CategoryTreeWithTreeSelector}
