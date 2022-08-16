import React, {useState} from 'react';
import styled from 'styled-components';
import {ConversationalHelper, Menu} from '../../components';
import {FormattedMessage, useIntl} from 'react-intl';
import {FileDroppingPlaceholder, FileInput} from './components';
import {useUploader} from './hooks';

const ProductFileDropping = () => {
    const [uploader] = useUploader();
    const intl = useIntl();
    const [fileUploadResult, setFileUploadResult] = useState<boolean>(false);

    const HeaderWelcomeMessage = (
        <>
            <p>
                <FormattedMessage defaultMessage="Welcome on your personnal data onboarding service." id="7L75AN" />
            </p>
            <p>
                <FormattedMessage
                    id="IEEx9e"
                    defaultMessage="Please share below your product information in a completed <b>XLSX file.</b>"
                    values={{
                        b: chunks => <b>{chunks}</b>,
                    }}
                />
            </p>
        </>
    );

    const HeaderSuccessMessage = (
        <p>
            <FormattedMessage defaultMessage="Your file was successfully shared, thank you!" id="a1Hc4g" />
        </p>
    );

    return (
        <Container>
            <Menu activeItem="fileEnrichment" />
            <Content>
                <ConversationalHelper content={fileUploadResult ? HeaderSuccessMessage : HeaderWelcomeMessage} />
                <FileInput
                    onFileUploaded={setFileUploadResult}
                    uploader={uploader}
                    placeholder={<FileDroppingPlaceholder />}
                    uploadingLabel={intl.formatMessage({defaultMessage: 'Uploading...', id: 'JEsxDw'})}
                    fileDraggingLabel={intl.formatMessage({
                        defaultMessage: 'Drag & drop your file here',
                        id: 'glWNCZ',
                    })}
                    uploadingPlaceholder={intl.formatMessage({
                        defaultMessage: 'It will be ready soon...',
                        id: 'EIj3Vt',
                    })}
                    uploadErrorLabel={intl.formatMessage({
                        defaultMessage: 'There was an error while uploading your file. please try again.',
                        id: 'eznI5f',
                    })}
                    generateUploadSuccessLabel={(filename: string) =>
                        //React Intl issue : see https://github.com/formatjs/formatjs/issues/3633#issuecomment-1181623008 and https://github.com/formatjs/formatjs/issues/3707
                        //@ts-ignore
                        intl.formatMessage(
                            {
                                defaultMessage: '<b>{filename}</b> was sucessfully shared.',
                                id: '+4C9Ii',
                            },
                            {
                                filename,
                                b: chunks => <b>{chunks}</b>,
                            }
                        )
                    }
                    uploadButtonLabel={intl.formatMessage({defaultMessage: 'Browse your files', id: '+WFftU'})}
                />
            </Content>
        </Container>
    );
};

const Container = styled.div`
    display: flex;
`;

const Content = styled.div`
    flex: 1;
    display: flex;
    flex-direction: column;
`;

export {ProductFileDropping};
