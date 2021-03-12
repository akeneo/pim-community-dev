import React from 'react';
import styled from 'styled-components';
import {AssociationTypesIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  margin-top: 120px;
  text-align: center;
`;

const Title = styled.div`
  font-size: ${({theme}) => theme.fontSize.title};
  color: ${({theme}) => theme.color.grey140};
  margin-top: 5px;
`;

// Fixme: Should be a link
const Hint = styled.div`
  font-size: ${({theme}) => theme.fontSize.bigger};
  color: ${({theme}) => theme.color.grey120};
  margin-top: 15px;
`;

const NoAssociationTypes = () => {
  const translate = useTranslate();

  return (
    <Container>
      <AssociationTypesIllustration />
      <Title>{translate('pim_enrich.entity.association_type.no_association_types.title')}</Title>
      <Hint>{translate('pim_enrich.entity.association_type.no_association_types.hint')}</Hint>
    </Container>
  );
};

export {NoAssociationTypes};
