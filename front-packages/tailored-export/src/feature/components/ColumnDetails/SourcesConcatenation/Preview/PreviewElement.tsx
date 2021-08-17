import React from 'react';
import styled from 'styled-components';
import {getColor, Placeholder} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../../models';
import {useAssociationType, useAttribute} from '../../../../hooks';

const SourceElementContainer = styled.span`
  color: ${getColor('grey', 140)};
`;

const StringElement = styled.span`
  color: ${getColor('blue', 100)};
`;

type SourceElementProps = {
  source: Source;
};

const AttributeSourceElement = ({source}: SourceElementProps) => {
  const [, attribute] = useAttribute(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (null === attribute) {
    return <Placeholder>{source.code}</Placeholder>;
  }

  return <SourceElementContainer>{getLabel(attribute.labels, catalogLocale, attribute.code)}</SourceElementContainer>;
};

const AssociationTypeSourceElement = ({source}: SourceElementProps) => {
  const [, associationType] = useAssociationType(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (null === associationType) {
    return <Placeholder>{source.code}</Placeholder>;
  }

  return (
    <SourceElementContainer>
      {getLabel(associationType.labels, catalogLocale, associationType.code)}
    </SourceElementContainer>
  );
};

const PropertySourceElement = ({source}: SourceElementProps) => {
  const translate = useTranslate();

  return <SourceElementContainer>{translate(`pim_common.${source.code}`)}</SourceElementContainer>;
};

export {AssociationTypeSourceElement, AttributeSourceElement, PropertySourceElement, StringElement};
