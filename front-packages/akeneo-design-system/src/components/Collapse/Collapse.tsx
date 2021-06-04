import React, {Ref, ReactNode} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import {IconButton} from '../../components';
import {CheckPartialIcon, PlusIcon} from '../../icons';

const CollapseContainer = styled.div`
  width: 100%;
`;

const Content = styled.div<{isOpen: boolean}>`
  transform: scaleY(${({isOpen}) => Number(isOpen)});
  transform-origin: top;
  transition: transform 0.2s ease-in-out;
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
    const collapse = () => onCollapse(!isOpen);

    return (
      <CollapseContainer ref={forwardedRef} {...rest}>
        <LabelContainer>
          <Label>{label}</Label>
          <IconButton
            size="small"
            level="tertiary"
            ghost="borderless"
            onClick={collapse}
            title={collapseButtonLabel}
            icon={isOpen ? <CheckPartialIcon /> : <PlusIcon />}
          />
        </LabelContainer>
        <Content isOpen={isOpen}>{children}</Content>
      </CollapseContainer>
    );
  }
);

export {Collapse};
