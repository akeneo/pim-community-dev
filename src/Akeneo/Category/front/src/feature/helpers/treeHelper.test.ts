import {aCategory, aCategoryTree, aCategoryTreeWithChildren} from '../../tests/provideCategoryHelper';
import {aTreeNode} from '../../tests/provideTreeNodeHelper';
import {buildNodesFromCategoryTree, findLoadedDescendantsIdentifiers} from './treeHelper';

describe('treeHelper', () => {
  test('it can build a list of nodes from a root category tree', () => {
    const aCategoryWithoutChildren = aCategoryTree('cat_without_children', [], false, true, 2);
    const anotherCategoryWithoutChildren = aCategoryTree('another_cat_without_children', [], false, true, 4);
    const aCategoryWithChildren = aCategoryTreeWithChildren(
      'cat_with_children',
      [anotherCategoryWithoutChildren],
      false,
      false,
      3
    );
    const root = aCategoryTreeWithChildren('a_root', [aCategoryWithoutChildren, aCategoryWithChildren], true, false, 1);

    const nodes = buildNodesFromCategoryTree(root);

    expect(nodes).toStrictEqual([
      {
        identifier: 1,
        label: '[a_root]',
        code: 'a_root',
        childrenIds: [2, 3],
        data: root,
        parentId: null,
        type: 'root',
        childrenStatus: 'loaded',
      },
      {
        identifier: 2,
        label: '[cat_without_children]',
        code: 'cat_without_children',
        childrenIds: [],
        data: aCategoryWithoutChildren,
        parentId: 1,
        type: 'leaf',
        childrenStatus: 'idle',
      },
      {
        identifier: 3,
        label: '[cat_with_children]',
        code: 'cat_with_children',
        childrenIds: [4],
        data: aCategoryWithChildren,
        parentId: 1,
        type: 'node',
        childrenStatus: 'loaded',
      },
      {
        identifier: 4,
        label: '[another_cat_without_children]',
        code: 'another_cat_without_children',
        childrenIds: [],
        data: anotherCategoryWithoutChildren,
        parentId: 3,
        type: 'leaf',
        childrenStatus: 'idle',
      },
    ]);
  });

  test('it finds identifiers of loaded descendants of a given node', () => {
    const categoryRoot = aCategory('root', undefined, 1, null);
    const nodeRoot = aTreeNode(categoryRoot, 1, [10, 11], 'a_tree', '', null, 'root', 'loaded');

    const whateverNode = aTreeNode(
      aCategory('whateverCategory', undefined, 10, categoryRoot),
      10,
      [101],
      'whateverNode',
      '',
      1,
      'node',
      'loaded'
    );

    const whateverNodeChild = aTreeNode(
      aCategory('whateverCategoryChild', undefined, 101, categoryRoot),
      101,
      [],
      'whateverNodeChild',
      '',
      10,
      'leaf',
      'idle'
    );

    const deletedNode = aTreeNode(
      aCategory('deletedCategory', undefined, 11, categoryRoot),
      11,
      [111, 112, 113],
      'deletedNode',
      '',
      1,
      'node',
      'loaded'
    );

    const deletedChildNodeLeaf = aTreeNode(
      aCategory('deletedChildCategory1', undefined, 111, categoryRoot),
      111,
      [],
      'deletedChildNodeLeaf',
      '',
      11,
      'leaf',
      'idle'
    );

    const deletedChildNodeWithoutLoadedChildren = aTreeNode(
      aCategory('deletedChildCategory2', undefined, 112, categoryRoot),
      112,
      [1121, 1122],
      'deletedChildNodeWithoutLoadedChildren',
      '',
      11,
      'node',
      'idle'
    );

    const deletedChildNodeWithLoadedChildren = aTreeNode(
      aCategory('deletedChildCategory3', undefined, 113, categoryRoot),
      113,
      [1131, 1132],
      'deletedChildNodeWithLoadedChildren',
      '',
      11,
      'node',
      'loaded'
    );

    const deletedGrandChildNode1 = aTreeNode(
      aCategory('deletedGrandChildCategory1', undefined, 1131, categoryRoot),
      1131,
      [],
      'deletedGrandChildNode1',
      '',
      113,
      'leaf',
      'idle'
    );

    const deletedGrandChildNode2 = aTreeNode(
      aCategory('deletedGrandChildCategory2', undefined, 1132, categoryRoot),
      1132,
      [],
      'deletedGrandChildNode2',
      '',
      113,
      'leaf',
      'idle'
    );

    const nodes = [
      nodeRoot,
      whateverNode,
      whateverNodeChild,
      deletedNode,
      deletedChildNodeLeaf,
      deletedChildNodeWithoutLoadedChildren,
      deletedChildNodeWithLoadedChildren,
      deletedGrandChildNode1,
      deletedGrandChildNode2,
    ];

    const result = findLoadedDescendantsIdentifiers(nodes, deletedNode);

    expect(result).toEqual([111, 112, 113, 1131, 1132]);
  });
});
