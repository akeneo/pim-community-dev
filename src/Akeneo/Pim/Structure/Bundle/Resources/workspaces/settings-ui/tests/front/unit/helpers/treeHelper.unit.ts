import {findLoadedDescendantsIdentifiers} from "@akeneo-pim-community/settings-ui/src/helpers";
import {aTreeNode} from "../../utils/provideTreeNodeHelper";
import {aCategory} from "../../utils/provideCategoryHelper";

describe('treeHelper', () => {
  test('it finds identifiers of loaded descendants of a given node', () => {
    const categoryRoot = aCategory('root', undefined, 1, null);
    const nodeRoot = aTreeNode(categoryRoot, 1, [10, 11], 'a_tree', null, 'root', 'loaded');

    const whateverCategory = aCategory('whateverCategory', undefined, 10, categoryRoot);
    const whateverNode = aTreeNode(whateverCategory, 10, [101], 'whateverNode', 1, 'node', 'loaded');

    const whateverCategoryChild = aCategory('whateverCategoryChild', undefined, 101, categoryRoot);
    const whateverNodeChild = aTreeNode(whateverCategoryChild, 101, [], 'whateverNodeChild', 10, 'leaf', 'idle');

    const deletedCategory = aCategory('deletedCategory', undefined, 11, categoryRoot);
    const deletedNode = aTreeNode(deletedCategory, 11, [111, 112, 113], 'deletedNode', 1, 'node', 'loaded');

    const deletedChildCategory1 = aCategory('deletedChildCategory1', undefined, 111, categoryRoot);
    const deletedChildNodeLeaf = aTreeNode(deletedChildCategory1, 111, [], 'deletedChildNodeLeaf', 11, 'leaf', 'idle');

    const deletedChildCategory2 = aCategory('deletedChildCategory2', undefined, 112, categoryRoot);
    const deletedChildNodeWithoutLoadedChildren = aTreeNode(deletedChildCategory2, 112, [1121, 1122], 'deletedChildNodeWithoutLoadedChildren', 11, 'node', 'idle');

    const deletedChildCategory3 = aCategory('deletedChildCategory3', undefined, 113, categoryRoot);
    const deletedChildNodeWithLoadedChildren = aTreeNode(deletedChildCategory3, 113, [1131, 1132], 'deletedChildNodeWithLoadedChildren', 11, 'node', 'loaded');

    const deletedGrandChildCategory1 = aCategory('deletedGrandChildCategory1', undefined, 1131, categoryRoot);
    const deletedGrandChildNode1 = aTreeNode(deletedGrandChildCategory1, 1131, [], 'deletedGrandChildNode1', 113, 'leaf', 'idle');

    const deletedGrandChildCategory2 = aCategory('deletedGrandChildCategory2', undefined, 1132, categoryRoot);
    const deletedGrandChildNode2 = aTreeNode(deletedGrandChildCategory2, 1132, [], 'deletedGrandChildNode2', 113, 'leaf', 'idle');

    const nodes = [nodeRoot, whateverNode, whateverNodeChild, deletedNode, deletedChildNodeLeaf, deletedChildNodeWithoutLoadedChildren, deletedChildNodeWithLoadedChildren, deletedGrandChildNode1, deletedGrandChildNode2];

    const result = findLoadedDescendantsIdentifiers(nodes, deletedNode);

    expect(result).toEqual([111, 112, 113, 1131, 1132]);
  });
});
