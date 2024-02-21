import React, {FC} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Template} from '../models';

const TemplateTitleContainer = styled.div`
  color: ${getColor('grey', 120)};
  font-size: ${getFontSize('default')};
  font-weight: 400;
  line-height: 16px;
  text-transform: uppercase;
  margin-top: 20px;
`;

const TemplateTitleLabel = styled.span`
  color: ${getColor('grey', 140)};
`;
TemplateTitleContainer.displayName = 'TemplateTitleContainer';
TemplateTitleLabel.displayName = 'TemplateTitleLabel';

type Props = {
  template: Template;
  locale: string | null;
};

const getLabelFromTemplate = (template: Template, locale: string): string => {
  return template.labels[locale] ?? '[' + template.code + ']';
};

const TemplateTitle: FC<Props> = ({template, locale}) => {
  const translate = useTranslate();

  const userContext = useUserContext();
  const catalogLocale = locale ? locale : userContext.get('catalogLocale');

  return (
    <TemplateTitleContainer>
      {translate('akeneo.category.edition_form.template.title')} :{' '}
      <TemplateTitleLabel>{getLabelFromTemplate(template, catalogLocale)}</TemplateTitleLabel>
    </TemplateTitleContainer>
  );
};

export {TemplateTitle};
