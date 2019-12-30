import React, {useContext, useRef} from 'react';
import {useImageUploader} from '../use-image-uploader';
import {useMediaUrlGenerator} from '../use-media-url-generator';
import {Translate, TranslateContext} from '../../shared/translate';
import styled from 'styled-components';
import {PropsWithTheme} from '../../common/theme';
import Trash from '../../common/assets/icons/trash';
import {isErr} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import defaultImageUrl from '../../common/assets/illustrations/api.svg';

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
const ImagePreview = styled.img`
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
    const containerClassName = `AknImage AknImage--editable AknImage--wide ${null === image ? 'AknImage--empty' : ''}`;
    const imageUploader = useImageUploader();
    const generateMediaUrl = useMediaUrlGenerator();
    const notify = useNotify();
    const translate = useContext(TranslateContext);
    const ref = useRef<HTMLInputElement>(null);

    const upload = async (file: File) => {
        const result = await imageUploader(file);
        if (isErr(result)) {
            if (undefined !== result.error.extension) {
                notify(NotificationLevel.ERROR, translate('pim_apps.edit_image.flash.extension_not_allowed'));
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
        if (null !== event.target.files) {
            const mediaUrl = await upload(event.target.files[0]);
            if (null !== mediaUrl) {
                onChange(mediaUrl);
            }
        }
    };
    const handleRemove = () => {
        onChange(null);
        if (null !== ref.current) {
            ref.current.value = '';
        }
    };

    const previewImage = image ? generateMediaUrl(image) : null;

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
                    <ImagePreview src={null === previewImage ? defaultImageUrl : previewImage} alt={''} />

                    <Helper className='AknImage-uploaderHelper'>
                        {null === previewImage && (
                            <>
                                <Translate id={'pim_apps.edit_image.upload_helper'} />{' '}
                                <HelperLink href='#'>
                                    <Translate id={'pim_apps.edit_image.click_here'} />
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
                            <Translate id={'pim_apps.edit_image.remove_helper'} />
                        </span>
                    </div>
                )}
            </Container>
        </>
    );
};

export default ImageUploader;
