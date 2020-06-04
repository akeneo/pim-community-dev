import React from 'react';
import styled from 'styled-components';

const Link = styled.a`
  border-radius: 16px;
  border: 1px solid #a1a9b7;
  height: 24px;
  padding: 4px 10px;
  line-height: 14px;
  margin-left: auto;
  color: #768096;
`;

type LinkProps = {
  baseUrl: string;
  campaign: string | null;
}

const buildLinkCardUrl = (baseUrl: string, campaign: string | null): URL => {
  const url = new URL(baseUrl);
  url.searchParams.append('utm_source', 'akeneo-app');
  url.searchParams.append('utm_medium', 'communication-panel');
  if (null !== campaign) {
    url.searchParams.append('utm_campaign', campaign);
  }

  return url;
}

const LinkComponent = ({baseUrl, campaign}: LinkProps) => {
  const url = buildLinkCardUrl(baseUrl, campaign);

  return (
    <Link href={url.href} target="_blank">
      Read More
    </Link>
  );
};

export = LinkComponent;
