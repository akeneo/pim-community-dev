import styled from 'styled-components';
import {Theme} from 'akeneo-design-system';
import React, {FC} from 'react';
import {useParams} from 'react-router-dom';
import LoginForm from './LoginForm';

const AuthenticationWrapper = styled.div<{theme?: Theme}>`
  color: ${props => props.theme.color.grey120};
  width: 100vw;
  height: 100vh;
  display: grid;
  grid-template-columns: 4fr 6fr;
  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const RightWrapper = styled.div<{theme?: Theme}>`
  display: grid;
  grid-template-rows: 1fr max-content;
  width: 100%;
  height: 100%;
  padding: 50px;
  box-sizing: border-box;
  @media (max-width: 768px) {
    padding: 40px;
  }
`;

const Logo = styled.div<{theme?: Theme}>`
  margin-bottom: 10px;
  display: flex;
  justify-content: center;
  img {
    max-width: 250px;
    max-height: 60px;
    @media (max-width: 768px) {
      max-height: 90px;
    }
  }
`;

const FormWrapper = styled.div<{theme?: Theme}>`
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  @media (max-width: 768px) {
    padding-bottom: 40px;
  }
`;

const ContainerForm = styled.div<{theme?: Theme}>`
  justify-content: center;
  display: grid;
  grid-template-rows: max-content 1fr;
  width: 100%;
`;

const Footer = styled.a<{theme?: Theme}>`
  text-decoration: none;
`;

const Powered = styled.div<{theme?: Theme}>`
  display: flex;
  justify-content: center;
  padding-bottom: 0.5em;
  font-size: 13px;
  align-items: flex-end;
  color: ${props => props.theme.color.grey140};
`;

const PoweredImage = styled.img<{theme?: Theme}>`
  max-height: 20px;
  margin-left: 0.5em;
`;

const BaseLine = styled.div<{theme?: Theme}>`
  color: ${props => props.theme.color.purple100};
  font-size: 13px;
  text-align: center;
`;

const LeftWrapper = styled.div<{theme?: Theme}>`
  background: ${props => props.theme.color.grey20};
  height: 100%;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  @media (max-width: 768px) {
    display: none;
  }
`;

const IllustrationLogin = styled.div<{theme?: Theme}>`
  background-image: url('/bundles/pimui/images/illustration-login.svg');
  background-repeat: no-repeat;
  height: 100%;
  width: 100%;
  background-size: 130%;
  background-position: center;
`;

const Container = styled.div``;

const Title = styled.h1`
  color: ${({theme}) => theme.color.brand100};
  font-size: ${({theme}) => theme.fontSize.title};
  font-weight: bold;
`;

const Page: FC = ({children}) => {
  return (
    <AuthenticationWrapper>
      <RightWrapper>
        <FormWrapper>
          <Logo>
            <img src={'/bundles/pimui/images/logo_login.svg'} alt={'Akeneo PIM'} />
          </Logo>
          <ContainerForm>{children}</ContainerForm>
        </FormWrapper>
        <Footer href="https://www.akeneo.com/">
          <Powered>
            {'Powered by'}
            <PoweredImage src={'/bundles/pimui/images/logo_login.svg'} alt="logo Akeneo" />
          </Powered>
          <BaseLine>{'Unlock Growth Through Product Experiences'}</BaseLine>
        </Footer>
      </RightWrapper>
      <LeftWrapper>
        <IllustrationLogin />
      </LeftWrapper>
    </AuthenticationWrapper>
  );
};

type LoginPageProps = {
  csrfToken: string;
};
const LoginPage: FC<LoginPageProps> = ({csrfToken}) => {
  return (
    <Page>
      <Container>
        <LoginForm csrfToken={csrfToken} />
      </Container>
    </Page>
  );
};

const ForgotPasswordPage: FC = () => {
  return (
    <Page>
      <Container>
        <Title>FORGOT PASSWORD PAGE</Title>
      </Container>
    </Page>
  );
};

const ForgotPasswordConfirmationPage: FC = () => {
  return (
    <Page>
      <Container>
        <Title>FORGOT PASSWORD CONFIRMATION PAGE</Title>
      </Container>
    </Page>
  );
};

const ResetPasswordPage: FC = () => {
  const {token} = useParams<{token: string}>();
  return (
    <Page>
      <Container>
        <Title>RESET PASSWORD PAGE</Title>
        <p>{token}</p>
      </Container>
    </Page>
  );
};

export {LoginPage, ForgotPasswordPage, ForgotPasswordConfirmationPage, ResetPasswordPage};
