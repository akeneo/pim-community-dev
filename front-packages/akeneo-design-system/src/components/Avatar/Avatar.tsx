import React, {useMemo} from 'react';
import styled, {css} from 'styled-components';
import {useTheme} from '../../hooks';
import {AkeneoThemedProps, getColor} from '../../theme';
import {AvatarProps} from './types';

const AvatarContainer = styled.span<AvatarProps & AkeneoThemedProps>`
  ${({size}) =>
    size === 'default'
      ? css`
          height: 32px;
          width: 32px;
          line-height: 32px;
          font-size: 15px;
          border-radius: 32px;
        `
      : css`
          height: 140px;
          width: 140px;
          line-height: 140px;
          font-size: 66px;
          border-radius: 140px;
        `}
  display: inline-block;
  color: ${getColor('white')};
  text-align: center;
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  text-transform: uppercase;
  cursor: ${({onClick}) => (onClick ? 'pointer' : 'default')};
`;

const Avatar = ({username, firstName, lastName, avatarUrl, size = 'default', ...rest}: AvatarProps) => {
  const theme = useTheme();

  const fallback = (
    firstName.trim().charAt(0) + lastName.trim().charAt(0) || username.substring(0, 2)
  ).toLocaleUpperCase();
  const title = `${firstName || ''} ${lastName || ''}`.trim() || username;

  const backgroundColor = useMemo(() => {
    const colorId = username.split('').reduce<number>((s, l) => s + l.charCodeAt(0), 0);
    const colors = [
      theme.colorAlternative.green120,
      theme.colorAlternative.darkCyan120,
      theme.colorAlternative.forestGreen120,
      theme.colorAlternative.oliveGreen120,
      theme.colorAlternative.blue120,
      theme.colorAlternative.darkBlue120,
      theme.colorAlternative.hotPink120,
      theme.colorAlternative.red120,
      theme.colorAlternative.coralRed120,
      theme.colorAlternative.yellow120,
      theme.colorAlternative.orange120,
      theme.colorAlternative.chocolate120,
    ];

    return colors[colorId % colors.length];
  }, [theme, username]);

  const style = avatarUrl ? {backgroundImage: `url(${avatarUrl})`} : {backgroundColor};

  return (
    <AvatarContainer size={size} {...rest} style={style} title={title}>
      {avatarUrl ? '' : fallback}
    </AvatarContainer>
  );
};

export {Avatar};
export type {AvatarProps};
