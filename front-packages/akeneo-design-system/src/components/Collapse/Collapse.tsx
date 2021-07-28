import React, {Ref, ReactNode, useRef, useState, useEffect} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import {IconButton} from '../../components';
import {CheckPartialIcon, PlusIcon} from '../../icons';
import {useIsMounted} from '../../hooks';

const ANIMATION_DURATION = 100;

const CollapseContainer = styled.div`
  width: 100%;
  border: solid ${getColor('grey', 40)};
  border-width: 0 0 1px 0;

  &:first-child {
    border-width: 1px 0;
  }
`;

const Content = styled.div<{$height: number; overflow: string; shouldAnimate: boolean}>`
  max-height: ${({$height}) => $height}px;
  overflow: ${({overflow}) => overflow};
  ${({shouldAnimate}) =>
    shouldAnimate &&
    `
    transition: max-height ${ANIMATION_DURATION}ms ease-in-out;
  `}
  padding-bottom: ${({$height}) => (0 === $height ? 0 : 10)}px;
`;

const LabelContainer = styled.div`
  height: 44px;
  display: flex;
  align-items: center;
`;

const Label = styled.div`
  flex: 1;
  text-transform: uppercase;
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('small')};
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
const Collapse = React.forwardRef<HTMLDivElement, CollapseProps>(
  (
    {label, collapseButtonLabel, isOpen, onCollapse, children, ...rest}: CollapseProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const [contentHeight, setContentHeight] = useState<number | null>(null);
    const [shouldAnimate, setShouldAnimate] = useState<boolean>(false);
    const contentRef = useRef<HTMLDivElement>(null);
    const isMounted = useIsMounted();

    const handleCollapse = () => onCollapse(!isOpen);

    useEffect(() => {
      if (!contentRef.current) return;
      if (0 === contentRef.current.scrollHeight) return;

      setContentHeight(contentRef.current.scrollHeight);
      const shouldAnimateTimeoutId = window.setTimeout(() => {
        if (!isMounted()) return;

        setShouldAnimate(true);
      }, ANIMATION_DURATION);

      return () => {
        window.clearTimeout(shouldAnimateTimeoutId);
      };
    }, [children]);

    return (
      <CollapseContainer ref={forwardedRef} {...rest}>
        <LabelContainer>
          <Label>{label}</Label>
          <IconButton
            size="small"
            level="tertiary"
            ghost="borderless"
            onClick={handleCollapse}
            title={collapseButtonLabel}
            icon={isOpen ? <CheckPartialIcon /> : <PlusIcon />}
          />
        </LabelContainer>
        <Content
          ref={contentRef}
          overflow={shouldAnimate || !isOpen || null === contentHeight ? 'hidden' : 'inherit'}
          $height={isOpen && null !== contentHeight ? contentHeight : 0}
          shouldAnimate={shouldAnimate && null !== contentHeight}
        >
          {children}
        </Content>
      </CollapseContainer>
    );
  }
);

export {Collapse};
