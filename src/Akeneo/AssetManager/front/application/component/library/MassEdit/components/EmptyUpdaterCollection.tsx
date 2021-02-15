import React from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, Link, RulesIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const EmptyUpdaterCollectionContainer = styled.div`
  display: flex;
  justify-content: center;
  padding: 20px;
  width: 100%;
  flex-direction: column;
  align-items: center;
`;

const Title = styled.h3`
  font-size: ${getFontSize('big')};
  color: ${getColor('grey', 140)};
  margin-bottom: 5px;
  font-weight: normal;
`;

const Body = styled.span`
  font-size: ${getFontSize('small')};
  color: ${getColor('grey', 100)};
`;

const EmptyUpdaterCollection = () => {
  const translate = useTranslate();

  return (
    <EmptyUpdaterCollectionContainer>
      <RulesIllustration size={128} />
      <Title>{translate('pim_asset_manager.asset.mass_edit.empty_selection.title')}</Title>
      <Body>
        {translate('pim_asset_manager.asset.mass_edit.empty_selection.body')}{' '}
        <Link href="#" target="_blank">
          {translate('pim_asset_manager.asset.mass_edit.empty_selection.link')}
        </Link>
      </Body>
    </EmptyUpdaterCollectionContainer>
  );
};

export {EmptyUpdaterCollection};
