import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../dependenciesTools/hooks';

const DEFAULT_ILLUSTRATION_URL =
  '/bundles/akeneopimruleengine/assets/illustrations/rules.svg';

const Container = styled.div`
  text-align: center;
  margin-top: 10px;
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.grey140};
`;

const LinkSection = styled.div`
  font-size: ${({theme}) => theme.fontSize.default};
  color: ${({theme}) => theme.color.grey100};
  margin-top: 5px;

  a {
    color: ${({theme}) => theme.color.purple100};
    text-decoration: underline;

    &:hover {
      text-decoration: none;
    }
  }
`;

const Illustration = styled.div<{url: string}>`
  width: 128px;
  height: 128px;
  background-image: url('${({url}) => url}');
  background-size: contain;
  margin: 0 auto;
`;

type Props = {
  illustrationUrl?: string;
};

const EmptySectionMessage: React.FC<Props> = ({
  illustrationUrl = DEFAULT_ILLUSTRATION_URL,
  children,
}) => {
  const translate = useTranslate();

  return (
    <Container>
      <Illustration url={illustrationUrl} />
      {children}
      <LinkSection>
        {translate('pimee_catalog_rule.form.edit.empty_section.doc')}{' '}
        <a
          href='https://help.akeneo.com/pim/serenity/articles/manage-your-rules.html#add-actions'
          target='blank'
          rel='noopener noreferrer'
        >
          {translate('pimee_catalog_rule.form.edit.empty_section.doc_link')}
        </a>
      </LinkSection>
    </Container>
  );
};

export {EmptySectionMessage};
