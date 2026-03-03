import React from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Link = styled.a`
  border-radius: 16px;
  border: 1px solid ${({theme}: AkeneoThemedProps) => theme.color.grey100};
  height: 24px;
  padding: 4px 10px;
  line-height: 14px;
  margin-left: auto;
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey120};
  text-transform: uppercase;
  margin-top: 20px;
`;

type LinkProps = {
  baseUrl: string;
  title: string;
  campaign: string | null;
  content: string;
};

const buildLinkAnnouncementUrl = (baseUrl: string, campaign: string | null, content: string): URL => {
  const url = new URL(baseUrl);
  url.searchParams.append('utm_source', 'akeneo-app');
  url.searchParams.append('utm_medium', 'communication-panel');
  url.searchParams.append('utm_content', content);

  if (null !== campaign) {
    url.searchParams.append('utm_campaign', campaign);
  }

  return url;
};

const LinkComponent = ({baseUrl, title, campaign, content}: LinkProps): JSX.Element => {
  const __ = useTranslate();
  const url = buildLinkAnnouncementUrl(baseUrl, campaign, content);

  return (
    <Link href={url.href} title={title} target="_blank">
      {__('pim_common.read_more')}
    </Link>
  );
};

export {LinkComponent};
