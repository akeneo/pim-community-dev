import React from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Styled} from '../components/Styled';
import {AddPropertyButton} from './structure/AddPropertyButton';
import styled from 'styled-components';

const StructureSectionTitle = styled(SectionTitle)`
  justify-content: space-between;
  margin-top: 20px;
  padding-bottom: 10px;
`;

const Structure: React.FC = () => {
  const translate = useTranslate();

  return (
    <>
      <StructureSectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.structure.title')}</SectionTitle.Title>
        <AddPropertyButton />
      </StructureSectionTitle>
      <Styled.FormContainer></Styled.FormContainer>
    </>
  );
};

export {Structure};
