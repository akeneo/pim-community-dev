import React, {Ref, ReactNode} from 'react';
import styled, { css } from 'styled-components';
import { ArrowDownIcon, CloseIcon } from "../../icons";
import { CommonStyle, getColor, getFontSize } from "../../theme";

const SwitcherButtonContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  > *:nth-child(2) {
    display: none;
  }
  &:hover > *:nth-child(2) {
    display: block;
  }
`;

const LabelAndValueContainer = styled.button<{inline: boolean}>`
  ${CommonStyle};
  border: none;
  background: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  ${({inline}) => inline ? css`
    align-items: baseline;
  ` : css`
    flex-direction: column;
  `}
`;

const Label = styled.label<{inline: boolean}>`
  cursor: pointer;
  ${({inline}) => inline ? css`
    margin-right: 3px;
    color: ${getColor('grey', 140)};
  ` : css`
    color: ${getColor('grey', 100)};
    text-transform: uppercase;
    font-size: ${getFontSize('small')};
    line-height: ${getFontSize('small')};
  `}
`;

const LabelAndArrow = styled.div`
  display: inline-flex;
  align-items: center;
`;

const Value = styled.span`
  color: ${getColor('purple', 100)};
  margin-right: 5px;
`

const CloseButton = styled.button`
  border: none;
  background: none;
  width: 20px;
  height: 20px;
  cursor: pointer;
  padding: 0;
`;

type SwitcherButtonProps = {
  label: string;
  onClick: () => void;
  inline?: boolean;
  children: ReactNode;
} & (
  {
    deletable: true;
    onDelete: () => void;
  } |
  {
    deletable?: false;
  }
);

/**
 * TODO.
 */
const SwitcherButton = React.forwardRef<HTMLDivElement, SwitcherButtonProps>(
  ({
    label,
    children,
    onClick,
    deletable = false,
    onDelete,
    inline = true,
    ...rest
  }: SwitcherButtonProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <SwitcherButtonContainer ref={forwardedRef} {...rest}>
        <LabelAndValueContainer onClick={onClick} inline={inline}>
          <Label inline={inline}>{ label }{ inline && ':'}</Label>
          <LabelAndArrow>
            <Value>{children}</Value>
            <ArrowDownIcon size={inline ? 16 : 10}/>
          </LabelAndArrow>
        </LabelAndValueContainer>
        { deletable &&
          <CloseButton>
            <CloseIcon size={10} onClick={onDelete}/>
          </CloseButton>
        }
      </SwitcherButtonContainer>
    );
  }
);

export {SwitcherButton};
