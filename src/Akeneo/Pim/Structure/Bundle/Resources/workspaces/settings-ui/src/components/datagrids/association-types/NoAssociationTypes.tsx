import React from 'react';
import styled from 'styled-components';
import {AssociationTypesIllustration, Link, getFontSize, getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  margin-top: 120px;
  text-align: center;
`;

const Title = styled.div`
  font-size: ${getFontSize('title')};
  margin-top: 5px;
`;

const Hint = styled.div`
  font-size: ${getFontSize('bigger')};
  color: ${getColor('grey', 120)};
  margin-top: 15px;
`;

const createAssociationType = (event: React.MouseEvent) => {
  event.preventDefault();
  event.stopPropagation();
  const createButton = document.getElementById('create-button-extension');
  if (null !== createButton) createButton.click();
};

const NoAssociationTypes = () => {
  const translate = useTranslate();

  return (
    <Container>
      <AssociationTypesIllustration />
      <Title>{translate('pim_enrich.entity.association_type.no_association_types.title')}</Title>
      <Hint>
        <Link onClick={createAssociationType}>
          {translate('pim_enrich.entity.association_type.no_association_types.hint')}
        </Link>
      </Hint>
    </Container>
  );
};

export {NoAssociationTypes};
