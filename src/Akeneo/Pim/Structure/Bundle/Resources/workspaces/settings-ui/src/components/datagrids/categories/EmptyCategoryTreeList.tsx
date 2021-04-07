import React from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, Link, ProductCategoryIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  height: 100%;
`;

const Title = styled.div`
  font-size: ${getFontSize('title')};
  color: ${getColor('grey', 140)};
  margin-top: 5px;
`;

const Hint = styled.div`
  font-size: ${getFontSize('big')};
  color: ${getColor('brand', 100)};
  margin-top: 5px;
  height: 18px;
  text-align: center;
`;

const EmptyCategoryTreeList = () => {
  const translate = useTranslate();

  return (
    <Container>
      <ProductCategoryIllustration />
      <Title>{translate('pim_enrich.entity.category.content.empty_tree_list.title')}</Title>
      <Hint>
        <Link
          href="https://help.akeneo.com/pim/serenity/articles/what-is-a-category.html#how-to-create-a-new-category"
          target="_blank"
        >
          {translate('pim_enrich.entity.category.content.empty_tree_list.hint')}
        </Link>
      </Hint>
    </Container>
  );
};

export {EmptyCategoryTreeList};
