import React, {useContext, useRef, useState} from 'react';
import {useImageUploader} from '../use-image-uploader';
import {useMediaUrlGenerator} from '../use-media-url-generator';
import {Translate, TranslateContext} from '../../shared/translate';
import styled from 'styled-components';
import {PropsWithTheme} from '../../common/theme';
import Trash from '../../common/assets/icons/trash';
import {isErr} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import defaultImageUrl from '../../common/assets/illustrations/api.svg';
import {Loading} from './Loading';

interface Props {
    image: string | null;
    onChange: (image: string | null) => void;
    onError: (error: string) => void;
}

const HelperLink = styled.a`
    color: ${({theme}: PropsWithTheme) => theme.color.blue100};
    text-decoration: underline;
    font-weight: 700;
`;
const Preview = styled.div`
    position: relative;
`;
const Image = styled.img`
    max-height: 120px;
    width: auto;
    margin-top: 20px;
`;
const Container = styled.div`
    flex-basis: 100%;
    height: auto;
`;
const Helper = styled.span`
    margin: 0 0 20px 0;
`;

const ImageUploader = ({image, onChange, onError}: Props) => {
    const [isLoading, setIsLoading] = useState(false);
    const [ratio, setRatio] = useState(0);
    const [uploadingImage, setUploadingImage] = useState();

    const handleDuringUpload = (e: {loaded: number; total: number}) => {
        const currentRatio = Math.round((e.loaded / e.total) * 100);
        setRatio(currentRatio);
    };
    const uploadImage = useImageUploader(handleDuringUpload);

    const generateMediaUrl = useMediaUrlGenerator();
    const notify = useNotify();
    const translate = useContext(TranslateContext);
    const ref = useRef<HTMLInputElement>(null);

    const startUpload = (file: File) => {
        const reader = new FileReader();
        reader.onload = (event: ProgressEvent<FileReader>) => {
            const target = event.target;
            if (null !== target) {
                setUploadingImage(target.result);
            }
        };
        reader.readAsDataURL(file);

        setIsLoading(true);
    };
    const endUpload = () => {
        setIsLoading(false);
        setRatio(0);
    };
    const upload = async (file: File) => {
        startUpload(file);
        const result = await uploadImage(file);
        endUpload();

        if (isErr(result)) {
            if (undefined !== result.error.extension) {
                notify(
                    NotificationLevel.ERROR,
                    translate('akeneo_connectivity.connection.edit_image.flash.extension_not_allowed')
                );
            } else {
                const errors = Object.entries(result.error).reduce((errors, [propertyPath, {message}]) => {
                    return errors + `${propertyPath}: ${message}`;
                }, '');
                onError(errors);
            }

            return null;
        }

        return result.value.filePath;
    };

    const handleInputChange = async (event: React.ChangeEvent<HTMLInputElement>) => {
        if (null !== event.target.files && event.target.files[0]) {
            const mediaUrl = await upload(event.target.files[0]);
            if (null !== mediaUrl) {
                onChange(mediaUrl);
            }
            setUploadingImage(undefined);
        }
    };

    const handleRemove = () => {
        onChange(null);
        if (null !== ref.current) {
            ref.current.value = '';
        }
    };

    let previewImage = null;
    if (undefined !== uploadingImage && 0 !== uploadingImage.length) {
        previewImage = uploadingImage;
    } else if (null !== image) {
        previewImage = generateMediaUrl(image);
    }

    const containerClassName = `AknImage AknImage--editable AknImage--wide ${null === image ? 'AknImage--empty' : ''}`;

    return (
        <>
            <Container className={containerClassName}>
                <input
                    type='file'
                    accept='.jpg, .jpeg, .gif, .png, .wbmp, .xbm, .webp, .bmp'
                    onChange={handleInputChange}
                    className='AknImage-updater'
                    ref={ref}
                />

                <div className='AknImage-uploader'>
                    <Preview>
                        <Image src={null === previewImage ? defaultImageUrl : previewImage} alt={''} />
                        {isLoading && <Loading ratio={ratio} />}
                    </Preview>
                    <Helper className='AknImage-uploaderHelper'>
                        {null === previewImage && (
                            <>
                                <Translate id={'akeneo_connectivity.connection.edit_image.upload_helper'} />{' '}
                                <HelperLink href='#'>
                                    <Translate id={'akeneo_connectivity.connection.edit_image.click_here'} />
                                </HelperLink>
                                .
                            </>
                        )}
                    </Helper>
                </div>

                {null !== previewImage && (
                    <div className='AknImage-action'>
                        <span className='AknImage-actionItem' onClick={handleRemove}>
                            <Trash color='#ffffff' className='AknImage-actionItemIcon' />{' '}
                            <Translate id={'akeneo_connectivity.connection.edit_image.remove_helper'} />
                        </span>
                    </div>
                )}
            </Container>
        </>
    );
};

export default ImageUploader;
