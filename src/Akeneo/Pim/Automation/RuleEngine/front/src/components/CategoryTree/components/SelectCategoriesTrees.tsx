import React from 'react';
import {useMenuState, Menu, MenuItem, MenuButton} from 'reakit/Menu';
import styled from 'styled-components';
import {CategoryTreeModel} from '../category-tree.types';
import {LocaleCode} from '../../../models';
import {useTranslate} from '../../../dependenciesTools/hooks';

const widthButtonAndContent = '150px';

const ArrowImg = styled.img`
  opacity: 0.5;
  padding: 0 5px;
  margin-left: auto;
`;

const CategoryButtonMenu = styled(MenuButton)`
  align-items: center;
  display: flex;
  background: none;
  border: 0;
  padding: 5px 5px;
  margin: 4px;
  width: auto;
  min-width: ${widthButtonAndContent};
`;

const CategoriesMenu = styled(Menu)`
  width: ${widthButtonAndContent};
  padding: 0 5px;
  background: ${props => props.theme.color.white};
  z-index: 1;
`;

const CategoryTop = styled.div`
  display: flex;
  justify-content: flex-end;
`;

const SelectedCategoryLabel = styled.span`
  color: ${props => props.theme.color.purple100};
`;

const CategoryTreeItem = styled.li`
  color: ${props => props.theme.color.grey140};
  :active,
  :hover {
    background: ${props => props.theme.color.grey60};
  }
`;

type Props = {
  categoryTrees: CategoryTreeModel[];
  currentCategoryTreeSelected: CategoryTreeModel;
  locale: LocaleCode;
  onClick: (category: CategoryTreeModel, event?: React.MouseEvent) => void;
};

const SelectCategoriesTrees: React.FC<Props> = ({
  categoryTrees,
  currentCategoryTreeSelected,
  locale,
  onClick,
}) => {
  const translate = useTranslate();
  const menu = useMenuState();
  // Styled components and reakit doesn't work here for some reasons.
  // So the css is inlined in the component.
  const styleMenuItem = {
    border: 'none',
    width: '100%',
    padding: '0',
    background: 'none',
  };
  const handleOnClick = (category: CategoryTreeModel) => (
    event: React.MouseEvent
  ) => {
    menu.hide();
    if (onClick) {
      onClick(category, event);
    }
  };
  return (
    <>
      <CategoryTop>
        <CategoryButtonMenu
          aria-haspopup='listbox'
          {...menu}
          onClick={() => menu.toggle()}>
          <span>
            {translate('pimee_catalog_rule.form.category.catalog')}:&nbsp;
          </span>
          <SelectedCategoryLabel>
            {currentCategoryTreeSelected.labels[locale]}
          </SelectedCategoryLabel>
          <ArrowImg
            src='bundles/pimui/images/jstree/icon-down.svg'
            alt={translate('pimee_catalog_rule.form.category.select')}
          />
        </CategoryButtonMenu>
      </CategoryTop>
      <CategoriesMenu
        {...menu}
        aria-label={translate('pimee_catalog_rule.form.category.menu')}>
        <ul tabIndex={-1} role='menu'>
          {categoryTrees.map(category => (
            <CategoryTreeItem key={category.code} role='none'>
              <MenuItem
                {...menu}
                as='button'
                onClick={handleOnClick(category)}
                style={styleMenuItem}>
                {category.labels[locale]}
              </MenuItem>
            </CategoryTreeItem>
          ))}
        </ul>
      </CategoriesMenu>
    </>
  );
};

export {SelectCategoriesTrees};
