import React, {HTMLAttributes, isValidElement, ReactElement, Ref} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {IconProps} from '../../icons';
import {Override} from '../../shared';

const Container = styled.div<{disabled: boolean; onClick: () => void} & AkeneoThemedProps>`
  min-height: 80px;
  border: 1px ${getColor('grey', 40)} solid;
  box-sizing: border-box;
  display: -ms-flexbox;
  display: inline-flex;
  opacity: ${({disabled}) => disabled && 0.5};
  cursor: ${({disabled, onClick}) => (disabled ? 'not-allowed' : onClick !== undefined ? 'pointer' : 'inherit')};
  background: ${getColor('white')}
}

;

:hover {
  border-color: ${({disabled}) => !disabled && getColor('grey', 60)};
  background: ${({disabled}) => !disabled && getColor('grey', 20)};
}

:active {
  outline: none;
  background: ${({disabled}) => !disabled && getColor('grey', 20)};
  border-color: ${({disabled}) => !disabled && getColor('grey', 80)};
}

:focus:not(:active) {
  box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  outline: none;
}
`;

const IconContainer = styled.div`
  min-width: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 1px ${getColor('grey', 60)} solid;
  margin: 10px 0;

  svg {
    color: ${getColor('grey', 100)};
  }
`;

const ContentContainer = styled.div`
  margin: 15px;
`;

const TruncableMixin = css`
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
`;

const Label = styled.div`
  color: ${getColor('brand', 100)};
  font-size: ${getFontSize('big')};
  margin-bottom: 2px;

  ${TruncableMixin};
`;

const Content = styled.div`
  color: ${getColor('grey', 100)};
  font-size: ${getFontSize('small')};

  ${TruncableMixin};
`;

const IconCardGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
`;

type IconCardProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Define the icon showed at left of the component.
     */
    icon: ReactElement<IconProps>;

    /**
     * The title to display
     */
    label: string | JSX.Element;

    /**
     * The content to display
     */
    content?: string;

    /**
     * Define if the component will be displayed as disabled
     */
    disabled?: boolean;

    /**
     * The callback when the user clicks on the card component
     */
    onClick?: () => void;
  }
>;

const IconCard: React.FC<IconCardProps> = React.forwardRef<HTMLDivElement, IconCardProps>(
  ({icon, label, content, onClick, disabled = false, ...rest}: IconCardProps, forwardedRef: Ref<HTMLDivElement>) => {
    const validIcon = isValidElement<IconProps>(icon) && React.cloneElement(icon, {size: 30});

    return (
      <Container ref={forwardedRef} disabled={disabled} onClick={onClick} {...rest}>
        <IconContainer>{validIcon}</IconContainer>
        <ContentContainer>
          <Label>{label}</Label>
          {content && <Content>{content}</Content>}
        </ContentContainer>
      </Container>
    );
  }
);

export {IconCard, IconCardGrid};
