import React from 'react';
import {TextInput, UnviewIcon, useBooleanState, ViewIcon} from "akeneo-design-system";
import styled, {css} from "styled-components";

type Props = {
    onChange: (value: string) => void;
    value: string;
};

const PasswordInput = ({onChange, value, ...rest}: Props) => {
    const [isPasswordVisible, display, hide] = useBooleanState(false);

    return (
        <Container>
            <TextInput type={isPasswordVisible ? 'text' : 'password'} onChange={onChange} value={value} {...rest} />
            {isPasswordVisible ?
                <StyledUnviewIcon size={16} onClick={() => isPasswordVisible ? hide() : display()}/> :
                <StyledViewIcon size={16} onClick={() => isPasswordVisible ? hide() : display()}/>
            }
        </Container>
    );
};

const Container = styled.div`
    position: relative;
`;

const IconStyle = css`
  position: absolute;
  top: 0;
  right: 0;
  margin: 12px 8px 12px 0;
  cursor: pointer;
`;

const StyledViewIcon = styled(ViewIcon)`
    ${IconStyle}
`;
const StyledUnviewIcon = styled(UnviewIcon)`
    ${IconStyle}
`;

export {PasswordInput};
