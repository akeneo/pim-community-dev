import React, {FC, useCallback} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, CloseIcon, getColor} from 'akeneo-design-system';
import {Category, CategoryCode} from '../../models/Category';
import {useTranslate} from '@akeneo-pim-community/shared';

const TagContainer = styled.ul<AkeneoThemedProps & {invalid: boolean}>`
    border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
    border-radius: 2px;
    padding: 4px;
    display: flex;
    flex-wrap: wrap;
    min-height: 40px;
    gap: 5px;
    box-sizing: border-box;
    background: ${getColor('white')};
    position: relative;
    width: 100%;
    margin: 0;
`;

const CategoryTag = styled.li<AkeneoThemedProps>`
    list-style-type: none;
    padding: 3px 17px 3px 4px;
    border: 1px ${getColor('grey', 80)} solid;
    background-color: ${getColor('grey', 20)};
    display: flex;
    align-items: center;
    height: 30px;
    box-sizing: border-box;
    max-width: 100%;
    z-index: 2;
`;

const TagText = styled.span`
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
`;

const RemoveTagIcon = styled(CloseIcon)<AkeneoThemedProps>`
    min-width: 12px;
    width: 12px;
    height: 12px;
    color: ${getColor('grey', 120)};
    margin-right: 2px;
    cursor: pointer;
`;

type Props = {
    selectedCategories: Category[];
    onRemove: (remainingCategories: Category[]) => void;
    isInvalid: boolean;
    onEmptySpaceClick: () => void;
};

const CategorySelection: FC<Props> = ({selectedCategories, onRemove, isInvalid, onEmptySpaceClick}) => {
    const translate = useTranslate();
    const removeTag = useCallback(
        (categoryToRemove: CategoryCode) => {
            const remainingCategories = [...selectedCategories].filter(category => category.code !== categoryToRemove);
            onRemove(remainingCategories);
        },
        [selectedCategories, onRemove]
    );

    const handleClick = (event: React.MouseEvent<HTMLElement>) => {
        if (event.currentTarget === event.target) {
            onEmptySpaceClick();
        }
    };

    return (
        <TagContainer invalid={isInvalid} onClick={handleClick} data-testid='category-selection'>
            {selectedCategories.map(category => (
                <CategoryTag key={category.code} data-testid={category.code}>
                    <RemoveTagIcon
                        onClick={() => removeTag(category.code)}
                        title={translate('akeneo_catalogs.product_selection.criteria.category.remove')}
                    />
                    <TagText>{category.label}</TagText>
                </CategoryTag>
            ))}
        </TagContainer>
    );
};

export {CategorySelection};
