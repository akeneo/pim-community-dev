import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, RulesIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Content = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 80px 0;
  padding: 20px;
  gap: 10px;
`;

const Title = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
  text-align: center;
`;

type SourcePlaceholderProps = {
  children?: ReactNode;
};

const SourcePlaceholder = ({children}: SourcePlaceholderProps) => {
  return (
    <Content>
      <RulesIllustration size={128} />
      <Title>{children}</Title>
    </Content>
  );
};

const DeletedAttributeSourcePlaceholder = () => {
  const translate = useTranslate();

  return (
    <SourcePlaceholder>
      {translate('akeneo.tailored_export.column_details.sources.deleted_attribute.title')}
    </SourcePlaceholder>
  );
};

const DeletedAssociationTypeSourcePlaceholder = () => {
  const translate = useTranslate();

  return (
    <SourcePlaceholder>
      {translate('akeneo.tailored_export.column_details.sources.deleted_association_type.title')}
    </SourcePlaceholder>
  );
};

const InvalidAttributeSourcePlaceholder = () => {
  const translate = useTranslate();

  return (
    <SourcePlaceholder>
      {translate('akeneo.tailored_export.column_details.sources.invalid_source.attribute')}
    </SourcePlaceholder>
  );
};

const InvalidAssociationTypeSourcePlaceholder = () => {
  const translate = useTranslate();

  return (
    <SourcePlaceholder>
      {translate('akeneo.tailored_export.column_details.sources.invalid_source.association_type')}
    </SourcePlaceholder>
  );
};

const InvalidPropertySourcePlaceholder = () => {
  const translate = useTranslate();

  return (
    <SourcePlaceholder>
      {translate('akeneo.tailored_export.column_details.sources.invalid_source.property')}
    </SourcePlaceholder>
  );
};

export {
  DeletedAssociationTypeSourcePlaceholder,
  DeletedAttributeSourcePlaceholder,
  InvalidAssociationTypeSourcePlaceholder,
  InvalidAttributeSourcePlaceholder,
  InvalidPropertySourcePlaceholder,
};
