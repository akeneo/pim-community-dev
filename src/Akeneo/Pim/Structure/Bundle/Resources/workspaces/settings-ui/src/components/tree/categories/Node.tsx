import React, {FC} from 'react';
import {Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Tree} from '../../shared';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {useCategoryTreeNode, useCountProductsBeforeDeleteCategory, useDrag, useDrop} from '../../../hooks';

type Props = {
  id: number;
  label: string;
  index?: number;
  sortable?: boolean;
  followCategory?: (category: CategoryTreeModel) => void;
  onCategoryMoved?: () => void;
  addCategory?: (parentCode: string, onCreate: () => void) => void;
  deleteCategory?: (identifier: number, label: string, numberOfProducts: number, onDelete: () => void) => void;
};

const Node: FC<Props> = ({id, label, followCategory, addCategory, deleteCategory, sortable = false, index = 0}) => {
  const {
    node,
    children,
    loadChildren,
    moveTo,
    onDeleteCategory,
    onCreateCategory,
    isOpen,
    open,
    close,
  } = useCategoryTreeNode(id);
  const {isDragged, isDraggable, ...dragProps} = useDrag(node, index);
  const {placeholderPosition, ...dropProps} = useDrop(node, index, moveTo);

  const translate = useTranslate();
  const countProductsBeforeDeleteCategory = useCountProductsBeforeDeleteCategory(id);

  if (node === undefined) {
    return null;
  }

  return (
    <Tree
      value={node}
      label={label}
      _isRoot={node.type === 'root'}
      isLeaf={node.type === 'leaf'}
      isLoading={node.childrenStatus === 'loading'}
      onClick={!followCategory ? undefined : ({data}) => followCategory(data)}
      disabled={isDragged()}
      selected={placeholderPosition === 'middle'}
      placeholderPosition={placeholderPosition}
      isOpen={node.type === 'root' ? true : isOpen}
      open={open}
      close={close}
      onOpen={async () => {
        if (node.childrenStatus !== 'idle') {
          return;
        }
        return loadChildren();
      }}
      draggable={isDraggable}
      {...dragProps}
      {...dropProps}
    >
      {(addCategory || deleteCategory) && (
        <Tree.Actions key={`category-actions-${id}`}>
          {addCategory && (
            <Button
              ghost
              level={'primary'}
              size="small"
              onClick={event => {
                event.stopPropagation();
                addCategory(node.data.code, onCreateCategory);
              }}
            >
              {translate('pim_enrich.entity.category.new_category')}
            </Button>
          )}
          {deleteCategory && node.type !== 'root' && (
            <Button
              ghost
              level={'danger'}
              size="small"
              onClick={event => {
                event.stopPropagation();
                countProductsBeforeDeleteCategory((nbProducts: number) =>
                  deleteCategory(id, label, nbProducts, onDeleteCategory)
                );
              }}
            >
              {translate('pim_common.delete')}
            </Button>
          )}
        </Tree.Actions>
      )}
      {children.map((child, index) => (
        <React.Fragment key={`category-node-${id}-${child.identifier}`}>
          <Node
            id={child.identifier}
            label={child.label}
            followCategory={followCategory}
            addCategory={addCategory}
            deleteCategory={deleteCategory}
            sortable={sortable}
            index={index}
          />
        </React.Fragment>
      ))}
    </Tree>
  );
};
export {Node};
