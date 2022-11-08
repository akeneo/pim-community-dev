import React from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {Styled} from '../components';
import {AddPropertyButton} from './structure/AddPropertyButton';

const Structure: React.FC = () => {
  const translate = useTranslate();

  return (
    <>
      <Styled.StructureSectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.structure.title')}</SectionTitle.Title>
        <AddPropertyButton />
      </Styled.StructureSectionTitle>
      <Styled.FormContainer></Styled.FormContainer>
    </>
  );
};

export {Structure};
