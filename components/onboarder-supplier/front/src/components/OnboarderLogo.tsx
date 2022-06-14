import React from 'react';
import styled from 'styled-components';
import logoImage from '../assets/images/onboarderlogo.svg';

const OnboarderLogo = () => {
    return <Logo src={logoImage} />;
};

const Logo = styled.img`
    width: 213px;
    margin-bottom: 30px;
`;

export {OnboarderLogo};
