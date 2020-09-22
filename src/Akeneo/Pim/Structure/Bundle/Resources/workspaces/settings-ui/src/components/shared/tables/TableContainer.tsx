import React, {FC} from 'react';
import styled from "styled-components";

type Props = {};

const Container = styled.div`
  position: relative;
  padding: 20px 0 10px 0;
`;

const TableContainer: FC<Props> = ({children}) => {
    return (
        <Container>{children}</Container>
    );
}

export {TableContainer};