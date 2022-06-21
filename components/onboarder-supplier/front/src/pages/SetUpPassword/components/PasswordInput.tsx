import React from 'react';
import {TextInput, TextInputProps, UnviewIcon, useBooleanState, ViewIcon} from 'akeneo-design-system';
import styled, {css} from 'styled-components';

type Props = TextInputProps;

const PasswordInput = ({...rest}: Props) => {
    const [isPasswordVisible, display, hide] = useBooleanState(false);

    const togglePasswordVisibility = () => (isPasswordVisible ? hide() : display());

    return (
        <Container>
            <TextInput type={isPasswordVisible ? 'text' : 'password'} {...rest} data-testid="password-input" />

            {isPasswordVisible ? (
                <StyledUnviewIcon size={16} onClick={togglePasswordVisibility} data-testid="hide-password" />
            ) : (
                <StyledViewIcon size={16} onClick={togglePasswordVisibility} data-testid="show-password" />
            )}
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
