import React from 'react';
import styled from 'styled-components';
import {getFontSize} from 'akeneo-design-system';

const Routing = require('pim/router');

interface Props {
  picture: string;
  name: string;
}

export function Avatar(props: Props) {
  const pictureUrl =
    props.picture !== null
      ? Routing.generate('pim_enrich_media_show', {
          filename: encodeURIComponent(props.picture),
          filter: 'thumbnail_small',
        })
      : 'bundles/pimui/images/info-user.png';

  return (
    <Container>
      <Image src={pictureUrl ?? 'bundles/pimui/images/info-user.png'} title={props.name} />
    </Container>
  );
}

const Container = styled.div`
  display: flex;
  align-items: center;
  font-size: ${getFontSize('big')};
  gap: 1ch;
`;

const Image = styled.img`
  width: 32px;
  height: 32px;
  border-radius: 50%;
`;
