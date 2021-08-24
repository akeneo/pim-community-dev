import React from 'react';
import {Placeholder} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Source} from '../../../../models';
import {useAssociationType, useAttribute} from '../../../../hooks';

type SourceElementProps = {
  source: Source;
};

const AttributeSourceElement = ({source}: SourceElementProps) => {
  const [isFetching, attribute] = useAttribute(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (isFetching) {
    return <Placeholder as="span">{source.code}</Placeholder>;
  }

  return <>{getLabel(attribute?.labels ?? {}, catalogLocale, source.code)}</>;
};

const AssociationTypeSourceElement = ({source}: SourceElementProps) => {
  const [isFetching, associationType] = useAssociationType(source.code);
  const catalogLocale = useUserContext().get('catalogLocale');

  if (isFetching) {
    return <Placeholder as="span">{source.code}</Placeholder>;
  }

  return <>{getLabel(associationType?.labels ?? {}, catalogLocale, source.code)}</>;
};

const PropertySourceElement = ({source}: SourceElementProps) => {
  const translate = useTranslate();

  return <>{translate(`pim_common.${source.code}`)}</>;
};

export {AssociationTypeSourceElement, AttributeSourceElement, PropertySourceElement};
