import React from 'react';
import styled from 'styled-components';
import {getColor, Placeholder} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../../models';
import {useAssociationType, useAttribute} from '../../../../hooks';

const SourceElementContainer = styled.span`
  color: ${getColor('grey', 140)};
`;

const TextElement = styled.span`
  color: ${getColor('blue', 100)};
`;

type SourceElementProps = {
  source: Source;
};

const AttributeSourceElement = ({source}: SourceElementProps) => {
  const [isFetching, attribute] = useAttribute(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (isFetching) {
    return <Placeholder as="span">{source.code}</Placeholder>;
  }

  return (
    <SourceElementContainer>{getLabel(attribute?.labels ?? {}, catalogLocale, source.code)}</SourceElementContainer>
  );
};

const AssociationTypeSourceElement = ({source}: SourceElementProps) => {
  const [isFetching, associationType] = useAssociationType(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (isFetching) {
    return <Placeholder as="span">{source.code}</Placeholder>;
  }

  return (
    <SourceElementContainer>
      {getLabel(associationType?.labels ?? {}, catalogLocale, source.code)}
    </SourceElementContainer>
  );
};

const PropertySourceElement = ({source}: SourceElementProps) => {
  const translate = useTranslate();

  return <SourceElementContainer>{translate(`pim_common.${source.code}`)}</SourceElementContainer>;
};

export {AssociationTypeSourceElement, AttributeSourceElement, PropertySourceElement, TextElement};
