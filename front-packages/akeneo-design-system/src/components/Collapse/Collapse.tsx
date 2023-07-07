import React, {Ref, ReactNode, useRef, useState, useEffect} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import {IconButton} from '../IconButton/IconButton';
import {CheckPartialIcon} from '../../icons/CheckPartialIcon';
import {PlusIcon} from '../../icons/PlusIcon';

const ANIMATION_DURATION = 100;

const CollapseContainer = styled.div`
  width: 100%;
  border: solid ${getColor('grey', 40)};
  border-width: 0 0 1px 0;

  &:first-child {
    border-width: 1px 0;
  }
  padding-bottom: ${({isOpen}) => (isOpen ? '10px' : 0)};
`;

const Content = styled.div<{$height: number; $overflow: string; shouldAnimate: boolean}>`
  max-height: ${({$height}) => $height}px;
  overflow: ${({$overflow}) => $overflow};
  ${({shouldAnimate}) =>
    shouldAnimate &&
    `
    transition: max-height ${ANIMATION_DURATION}ms ease-in-out;
  `}
`;

const LabelContainer = styled.div`
  height: 44px;
  padding-right: 2px; // To manage the outline of the collapse icon being cropped in case of overflow hidden
  display: flex;
  align-items: center;
  cursor: pointer;
`;

const Label = styled.div`
  flex: 1;
  text-transform: uppercase;
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('default')};
  display: flex;
  align-items: center;
  gap: 10px;
`;

type CollapseProps = {
  /**
   * The label of the Collapse.
   */
  label: ReactNode;

  /**
   * Label of the collapse button.
   */
  collapseButtonLabel: string;

  /**
   * Whether or not the Collapse is open.
   */
  isOpen: boolean;

  /**
   * Handler called when the collapse button is clicked.
   */
  onCollapse: (isOpen: boolean) => void;

  /**
   * Content of the Collapse.
   */
  children?: ReactNode;
};

/**
 * The collapse is used to organise groups of elements and possibly hide them.
 */
const Collapse: React.FC<CollapseProps & {ref?: React.Ref<HTMLDivElement>}> = React.forwardRef<HTMLDivElement, CollapseProps>(
  (
    {label, collapseButtonLabel, isOpen, onCollapse, children, ...rest}: CollapseProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const [contentHeight, setContentHeight] = useState<number>(0);
    const [shouldAnimate, setShouldAnimate] = useState<boolean>(false);
    const contentRef = useRef<HTMLDivElement>(null);

    const handleCollapse = () => onCollapse(!isOpen);

    useEffect(() => {
      setContentHeight(contentHeight => {
        const scrollHeight = contentRef.current?.scrollHeight ?? 0;

        return 0 === scrollHeight ? contentHeight : scrollHeight;
      });

      const shouldAnimateTimeoutId = window.setTimeout(() => {
        setShouldAnimate(true);
      }, ANIMATION_DURATION);

      return () => {
        window.clearTimeout(shouldAnimateTimeoutId);
      };
    }, [children]);

    return (
      <CollapseContainer ref={forwardedRef} isOpen={isOpen} {...rest}>
        <LabelContainer onClick={handleCollapse}>
          <Label>{label}</Label>
          <IconButton
            size="small"
            level="tertiary"
            ghost="borderless"
            title={collapseButtonLabel}
            icon={isOpen ? <CheckPartialIcon /> : <PlusIcon />}
          />
        </LabelContainer>
        <Content
          ref={contentRef}
          $overflow={shouldAnimate || !isOpen ? 'hidden' : 'inherit'}
          $height={isOpen ? contentHeight : 0}
          shouldAnimate={shouldAnimate}
        >
          {children}
        </Content>
      </CollapseContainer>
    );
  }
);

export {Collapse};
