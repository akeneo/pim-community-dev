import React, {FC} from 'react';
import {ClientErrorIllustration} from 'akeneo-design-system';
import {Translate} from '../../shared/translate';
import styled from '../../common/styled-with-theme';

const Message = styled.div`
    height: 36px;
    font-size: ${({theme}) => theme.fontSize.title};
    color: ${({theme}) => theme.color.grey140};
    margin: 40px auto 0;
    width: 735px;
`;
const Illustration = styled.div`
    margin: 200px auto 0;
    vertical-align: middle;
    width: 525px;
`;

export const UnreachableMarketplace: FC = () => {
    return (
        <>
            <Illustration>
                <ClientErrorIllustration size={525} height={255} />
            </Illustration>
            <Message>
                <Translate id='akeneo_connectivity.connection.connect.marketplace.unreachable' />
            </Message>
        </>
    );
};
