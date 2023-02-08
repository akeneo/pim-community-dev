import React, {FC} from 'react';
import {Button} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Tree} from './base';
import {CategoryTreeModel} from '../../models';
import {useCategoryTreeNode, useDragTreeNode, useDropTreeNode, useCountProductsBeforeDeleteCategory} from '../../hooks';

type Props = {
  id: number;
  label: string;
  code: string;
  index?: number;
  orderable?: boolean;
  followCategory?: (category: CategoryTreeModel) => void;
  onCategoryMoved?: () => void;
  addCategory?: (parentCode: string, onCreate: () => void) => void;
  deleteCategory?: (
    identifier: number,
    label: string,
    code: string,
    numberOfProducts: number,
    onDelete: () => void
  ) => void;
};

const Node: FC<Props> = ({
  id,
  label,
  code,
  followCategory,
  addCategory,
  deleteCategory,
  orderable = false,
  index = 0,
}) => {
  const {node, children, loadChildren, moveTo, onDeleteCategory, onCreateCategory, isOpen, open, close} =
    useCategoryTreeNode(id);
  const {isDragged, isDraggable, ...dragProps} = useDragTreeNode(node, index);
  const {placeholderPosition, ...dropProps} = useDropTreeNode(node, moveTo);

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
      open={() => {
        open();
        if (node.childrenStatus === 'idle') {
          loadChildren();
        }
      }}
      close={close}
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
                  deleteCategory(id, label, code, nbProducts, onDeleteCategory)
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
            code={child.code}
            followCategory={followCategory}
            addCategory={addCategory}
            deleteCategory={deleteCategory}
            orderable={orderable}
            index={index}
          />
        </React.Fragment>
      ))}
    </Tree>
  );
};
export {Node};
