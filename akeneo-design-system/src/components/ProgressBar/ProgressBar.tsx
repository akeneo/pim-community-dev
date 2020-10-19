import React, {Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getColorForLevel, getFontSize, Level} from '../../theme';

const ProgressBarContainer = styled.div``;

const Header = styled.div`
  display: flex;
  align-items: stretch;
  flex-direction: row;
  font-size: ${getFontSize('default')};
`;

const Title = styled.div`
  color: ${getColor('grey140')};
  flex-shrink: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex-grow: 1;
  padding-right: 20px;
`;

const ProgressLabel = styled.div`
  color: ${getColor('grey120')};
  flex-shrink: 0;
`;

const ProgressBarBackground = styled.div<{height: ProgressBarHeight} & AkeneoThemedProps>`
  background: ${getColor('grey80')};
  height: ${props => getHeight(props.height)};
  overflow: hidden;
  position: relative;
`;

const ProgressBarFill = styled.div.attrs<{width: number; level: Level}>(props => ({
  style: {width: props.width + '%'},
}))<{level: Level}>`
  ${({level}: {level: Level} & AkeneoThemedProps) => css`
    background: ${getColorForLevel(level, 100)};
  `}

  height: 100%;
  left: 0;
  position: absolute;
  top: 0;
  transition: width 0.3s;
`;

const getHeight = (height: ProgressBarHeight): string => {
  switch (height) {
    case 'large':
      return '10px';
    case 'small':
    default:
      return '4px';
  }
};

const sanitizePercent = (percent: number): number => {
  if (percent < 0) {
    return 0;
  }

  if (percent > 100) {
    return 100;
  }

  return percent;
};

type ProgressBarHeight = 'small' | 'large';

type ProgressBarProps = {
  /**
   * Define the level of the progress bar.
   */
  level: Level;

  /**
   * The progression of the progress bar in percentage (from 0 to 100).
   */
  percent: number;

  /**
   * Is the style of the progress bar should be light
   */
  light: boolean;

  /**
   * The progress bar title.
   */
  title?: string;

  /**
   * Describe the progress with a label (example: 46%, 30 minutes left).
   */
  progressLabel?: string;

  /**
   * Define the height of the progress bar.
   */
  height?: ProgressBarHeight;
} & React.HTMLAttributes<HTMLDivElement>;

/**
 * Progress bar to provide users with feedback on what is going on.
 */
const ProgressBar = React.forwardRef<HTMLDivElement, ProgressBarProps>(
  (
    {level, percent, title, progressLabel, height = 'small', ...rest}: ProgressBarProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    return (
      <ProgressBarContainer ref={forwardedRef} {...rest}>
        {(title || progressLabel) && (
          <Header>
            <Title>{title}</Title>
            <ProgressLabel>{progressLabel}</ProgressLabel>
          </Header>
        )}
        <ProgressBarBackground
          role="progressbar"
          aria-valuenow={sanitizePercent(percent)}
          aria-valuemin="0"
          aria-valuemax="100"
          height={height}
        >
          <ProgressBarFill level={level} width={sanitizePercent(percent)} />
        </ProgressBarBackground>
      </ProgressBarContainer>
    );
  }
);

export {ProgressBar};
