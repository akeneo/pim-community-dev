import React from 'react';
import {FormattedMessage} from 'react-intl';
import {Button, getFontSize} from 'akeneo-design-system';
import styled from "styled-components";
import {useUserContext} from "../contexts";
import {routes} from "./routes";
import {useHistory} from "react-router-dom";

const NotFound = () => {
    const {isAuthenticated} = useUserContext();
    const history = useHistory();

    const goToHome = () => {
        const route = isAuthenticated ? routes.home : routes.login;
        history.push(route);
    }

    return (
        <Container>
            <h1>
                <FormattedMessage defaultMessage="A 404 error occurred..." id="qPuNKQ" />
            </h1>
            <Message>
                <FormattedMessage defaultMessage="Page not found" id="QRccCM" />
            </Message>
            <StyledButton level="secondary" onClick={goToHome}>
                <FormattedMessage defaultMessage="Return to home page" id="ZFDHcN" />
            </StyledButton>
        </Container>
    );
};

const Container = styled.div`
  max-width: 940px;
  margin: auto;
  text-align: center;
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 20px
`;

const Message = styled.div`
  border: 1px solid #c1422f;
  background: #f2cfca;
  color: #983425;
  text-align: center;
  font-size: ${getFontSize('big')};
  padding: 10px;
`;

const StyledButton = styled(Button)`
    text-transform: none;
    font-size: 18px !important;
    width: auto;
    align-self: center;
`;

export {NotFound};
