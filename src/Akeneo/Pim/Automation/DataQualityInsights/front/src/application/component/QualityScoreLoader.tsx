import styled from 'styled-components';
import React from "react";
import {
  LoadingSpinner
} from "@akeneo-pim-community/connectivity-connection/src/common/components/Loading/LoadingSpinner";

const QualityScoreLoader = () => {
  return (
    <Container>
      <LoadingSpinner/>
    </Container>
  );
};

const Container = styled.div`
  display: flex;
  position: relative;
  top: 1px;
  border-right: 1px ${({theme}) => theme.color.grey80} solid;
  padding-right: 20px;
  margin-right: 20px;
  padding-top: 2px;
  height: 25px;
  cursor: pointer;

  > :first-child {
    border-radius: 4px 0 0 4px;
  }

  > :last-child {
    border-radius: 0 4px 4px 0;
  }

  > :not(:first-child):not(:last-child) {
    border-radius: 0;
  }
`;

export {QualityScoreLoader};
