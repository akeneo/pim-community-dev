import React from "react";
import styled from "styled-components";

const OnboarderLogo = () => {
    return (
        <Logo src="/assets/images/onboarderlogo.svg"/>
    );
};

const Logo = styled.img`
    width: 213px;
    margin-bottom: 30px;
`;

export {OnboarderLogo};
