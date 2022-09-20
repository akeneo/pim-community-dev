import React, {SyntheticEvent} from 'react';
import {FormattedMessage} from 'react-intl';
import styled from 'styled-components';
import {Link} from 'akeneo-design-system';
import {routes} from '../../routes';
import {useHistory} from 'react-router-dom';
import {ConversationalHelper} from '../../../components';

const Container = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    margin-top: 0;
`;

const EmptyProductFileHistory = () => {
    const history = useHistory();

    const goToFilesDroppingPage = (event: SyntheticEvent) => {
        event.preventDefault();

        history.push(routes.filesDropping);
    };

    const HeaderWelcomeMessage = (
        <>
            <p>
                <FormattedMessage defaultMessage="You will find here a recap of the files you shared." id="VeYJWI" />
            </p>
        </>
    );

    return (
        <>
            <ConversationalHelper content={HeaderWelcomeMessage} />
            <Container>
                <FormattedMessage defaultMessage="Your file history is empty." id="nZPPr0" />

                <Link onClick={goToFilesDroppingPage}>
                    <FormattedMessage defaultMessage="Please share an XLSX file first." id="KdWtos" />
                </Link>
            </Container>
        </>
    );
};

export {EmptyProductFileHistory};
