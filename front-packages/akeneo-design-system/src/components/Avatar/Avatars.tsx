import React, {Children} from 'react';
import styled from 'styled-components';
import {Override} from '../../shared';
import {AkeneoThemedProps, getColor} from '../../theme';

const AvatarListContainer = styled.div<AvatarsProps & AkeneoThemedProps>`
  display: flex;
  flex-direction: row-reverse;
  justify-content: flex-end;
  & > * {
    margin-right: -4px;
    position: relative;
  }
`;

const RemainingAvatar = styled.span`
  height: 32px;
  width: 32px;
  display: inline-block;
  border: 1px solid ${getColor('grey', 10)};
  line-height: 32px;
  text-align: center;
  font-size: 15px;
  border-radius: 32px;
  background-color: ${getColor('white')};
`;

type AvatarsProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    max: number;
  }
>;

const Avatars = ({max, children, ...rest}: AvatarsProps) => {
  const childrenArray = Children.toArray(children);
  const displayedChildren = childrenArray.slice(0, max);
  const remainingChildrenCount = childrenArray.length - max;
  const reverseChildren = displayedChildren.reverse();

  return (
    <AvatarListContainer {...rest}>
      {remainingChildrenCount > 0 && <RemainingAvatar>+{remainingChildrenCount}</RemainingAvatar>}
      {reverseChildren}
    </AvatarListContainer>
  );
};

export {Avatars};
export type {AvatarsProps};
