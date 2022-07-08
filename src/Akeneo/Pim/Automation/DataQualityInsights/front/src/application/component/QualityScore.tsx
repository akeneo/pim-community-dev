import React, {FC, HTMLAttributes} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, Override} from 'akeneo-design-system';
import {allScoreValues, QualityScoreValue} from '../../domain';

type Rounded = 'all' | 'left' | 'right' | 'none';

type Props = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    score: QualityScoreValue;
    size?: 'normal' | 'big';
    stacked?: boolean;
    rounded?: Rounded;
  }
>;

const roundedProperties = {
  all: '4px',
  left: '4px 0 0 4px',
  right: '0 4px 4px 0',
  none: '0',
};

type ColorProperty = {
  [score: string]: {
    backgroundColor: string;
    color: string;
    stackedBorderColor: string;
  };
};

const colorProperties: ColorProperty = {
  A: {
    backgroundColor: 'green20',
    color: 'green120',
    stackedBorderColor: 'green60',
  },
  B: {
    backgroundColor: 'green60',
    color: 'green140',
    stackedBorderColor: 'green100',
  },
  C: {
    backgroundColor: 'yellow20',
    color: 'yellow120',
    stackedBorderColor: 'yellow60',
  },
  D: {
    backgroundColor: 'red20',
    color: 'red100',
    stackedBorderColor: 'red40',
  },
  E: {
    backgroundColor: 'red60',
    color: 'red140',
    stackedBorderColor: 'red100',
  },
};

/**
 * <QualityScore score={'A'} />
 *
 * <QualityScore score={'A'} size={'big'} />
 *
 * <QualityScore score={'A'} size={'big'} rounded={'left'} stacked />
 *
 */
const QualityScore: FC<Props> = ({score, size = 'normal', rounded = 'all', stacked = false, ...props}) => {
  return stacked ? (
    <Wrapper size={size}>
      <EmptyContainer score={score} size={size} top={-2} left={4} data-testid="empty-container-back" />
      <EmptyContainer score={score} size={size} top={0} left={2} data-testid="empty-container-middle" />
      <Container score={score} size={size} rounded={rounded} stacked={stacked} {...props}>
        {score}
      </Container>
    </Wrapper>
  ) : (
    <Container score={score} size={size} rounded={rounded} {...props}>
      {score}
    </Container>
  );
};

const Wrapper = styled.div<{size: string}>`
  position: relative;
  width: ${({size}) => (size === 'big' ? '25px' : '20px')};
  height: ${({size}) => (size === 'big' ? '25px' : '20px')};
  margin: -2px 2px 0 -2px;
`;

const containerStackedStyled = css<{score: QualityScoreValue; size: string}>`
  position: absolute;
  top: 2px;
  left: 2px;
  border: 1px solid
    ${({theme, score}) => allScoreValues.includes(score) && theme.color[colorProperties[score].stackedBorderColor]};
  border-radius: ${roundedProperties['all']};

  ${({size}) =>
    size === 'normal' &&
    css`
      left: 0 !important;
    `};
`;

const Container = styled.div<{score: QualityScoreValue; size: string; rounded: Rounded; stacked?: boolean}>`
  text-align: center;
  display: inline-block;
  text-transform: uppercase;
  font-weight: bold;
  width: 20px;
  height: 20px;
  font-size: 13px;
  background-color: ${({theme, score}) =>
    allScoreValues.includes(score) && theme.color[colorProperties[score].backgroundColor]};
  color: ${({theme, score}) => allScoreValues.includes(score) && theme.color[colorProperties[score].color]};
  border-radius: ${({rounded}) => roundedProperties[rounded]};

  ${({size}) =>
    size === 'big' &&
    css`
      width: 25px;
      height: 25px;
      font-size: 15px;
      line-height: 25px;
      top: -2px;
      position: relative;
      margin: 0 -2px 0 -2px;
      border-radius: ${roundedProperties['all']};
    `};

  ${({stacked}) => stacked && containerStackedStyled}
`;
Container.defaultProps = {
  stacked: false,
  rounded: 'all',
};

const EmptyContainer = styled.div<
  {score: QualityScoreValue; size: string; top: number; left: number} & AkeneoThemedProps
>`
  top: ${({top}) => top}px;
  left: ${({left}) => left}px;
  position: absolute;
  display: inline-block;
  width: ${({size}) => (size === 'big' ? '25px' : '20px')};
  height: ${({size}) => (size === 'big' ? '25px' : '20px')};
  border-radius: 4px !important;
  border: 1px solid
    ${({theme, score}) => allScoreValues.includes(score) && theme.color[colorProperties[score].stackedBorderColor]};
  background-color: ${getColor('white')};
`;

export {QualityScore};
