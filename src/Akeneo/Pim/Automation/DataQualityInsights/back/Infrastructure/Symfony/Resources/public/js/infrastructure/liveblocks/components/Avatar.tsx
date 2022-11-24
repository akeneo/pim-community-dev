import React from 'react';
import styled from 'styled-components';

const IMAGE_SIZE = 48;

interface Props {
  picture: string;
  name: string;
}

export function Avatar(props: Props) {
  return (
    <AvatarDiv data-tooltip={props.name}>
      <AvatarImg
        src={props.picture ?? 'bundles/pimui/images/info-user.png'}
        height={IMAGE_SIZE}
        width={IMAGE_SIZE}
      />
    </AvatarDiv>
  );
}

const AvatarDiv = styled.div`
  display: flex;
  place-content: center;
  position: relative;
  border: 4px solid #fff;
  border-radius: 9999px;
  width: 56px;
  height: 56px;
  background-color: #9ca3af;
  margin-left: -0.75rem;

  ::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    opacity: 0;
    transition: opacity 0.15s ease;
    padding: 5px 10px;
    color: white;
    font-size: 0.75rem;
    border-radius: 8px;
    margin-bottom: 10px;
    z-index: 1;
    background: black;
    white-space: nowrap;
  }

  :hover:before {
    opacity: 1;
  }
`;

const AvatarImg = styled.img`
  border-radius: 9999px;
`;
