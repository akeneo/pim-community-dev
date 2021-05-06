import {findLoadedDescendantsIdentifiers} from '@akeneo-pim-community/settings-ui/src/helpers';
import {aTreeNode} from '../../utils/provideTreeNodeHelper';
import {aCategory} from '../../utils/provideCategoryHelper';

describe('treeHelper', () => {
  test('it finds identifiers of loaded descendants of a given node', () => {
    const categoryRoot = aCategory('root', undefined, 1, null);
    const nodeRoot = aTreeNode(categoryRoot, 1, [10, 11], 'a_tree', null, 'root', 'loaded');

    const whateverNode = aTreeNode(
      aCategory('whateverCategory', undefined, 10, categoryRoot),
      10,
      [101],
      'whateverNode',
      1,
      'node',
      'loaded'
    );

    const whateverNodeChild = aTreeNode(
      aCategory('whateverCategoryChild', undefined, 101, categoryRoot),
      101,
      [],
      'whateverNodeChild',
      10,
      'leaf',
      'idle'
    );

    const deletedNode = aTreeNode(
      aCategory('deletedCategory', undefined, 11, categoryRoot),
      11,
      [111, 112, 113],
      'deletedNode',
      1,
      'node',
      'loaded'
    );

    const deletedChildNodeLeaf = aTreeNode(
      aCategory('deletedChildCategory1', undefined, 111, categoryRoot),
      111,
      [],
      'deletedChildNodeLeaf',
      11,
      'leaf',
      'idle'
    );

    const deletedChildNodeWithoutLoadedChildren = aTreeNode(
      aCategory('deletedChildCategory2', undefined, 112, categoryRoot),
      112,
      [1121, 1122],
      'deletedChildNodeWithoutLoadedChildren',
      11,
      'node',
      'idle'
    );

    const deletedChildNodeWithLoadedChildren = aTreeNode(
      aCategory('deletedChildCategory3', undefined, 113, categoryRoot),
      113,
      [1131, 1132],
      'deletedChildNodeWithLoadedChildren',
      11,
      'node',
      'loaded'
    );

    const deletedGrandChildNode1 = aTreeNode(
      aCategory('deletedGrandChildCategory1', undefined, 1131, categoryRoot),
      1131,
      [],
      'deletedGrandChildNode1',
      113,
      'leaf',
      'idle'
    );

    const deletedGrandChildNode2 = aTreeNode(
      aCategory('deletedGrandChildCategory2', undefined, 1132, categoryRoot),
      1132,
      [],
      'deletedGrandChildNode2',
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
