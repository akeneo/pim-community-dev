import React, {useContext} from 'react';
import {useImageUploader} from '../use-image-uploader';
import {useMediaUrlGenerator} from '../use-media-url-generator';
import {Translate, TranslateContext} from '../../shared/translate';
import styled from 'styled-components';
import {PropsWithTheme} from '../../common/theme';
import Trash from '../../common/assets/icons/trash';
import {isErr, isOk} from '../../shared/fetch/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import defaultImageUrl from '../../common/assets/illustrations/api.svg';

interface Props {
    image: string|null;
    onChange: (image: string|null) => void;
    onError: (error: string) => void;
}

const HelperLink = styled.a`
    color: ${({theme}: PropsWithTheme) => theme.color.blue};
    text-decoration: underline;
    font-weight: 700;
`;

const DefaultImage = styled.img`
    max-height: 140px;
    width: auto;
`;

const ImageUploader = ({image, onChange, onError}: Props) => {
    const containerClassName = `AknImage AknImage--editable AknImage--wide ${null === image ? 'AknImage--empty' : ''}`;
    const imageUploader = useImageUploader();
    const generateMediaUrl = useMediaUrlGenerator();
    const notify = useNotify();
    const translate = useContext(TranslateContext);

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

        if (isOk(result)) {
            return result.data.filePath;
        }
        return null;
    };

    const onInputChange = async (event: React.ChangeEvent<HTMLInputElement>) => {
        if (null !== event.target.files) {
            const mediaUrl = await upload(event.target.files[0]);
            if (null !== mediaUrl) {
                onChange(mediaUrl);
            }
        }
    };

    const previewImage = image ? generateMediaUrl(image) : null;

    return (
        <>
            <div className={containerClassName}>
                <input
                  type='file'
                  accept='.jpg, .jpeg, .gif, .png, .wbmp, .xbm, .webp, .bmp'
                  onChange={onInputChange}
                  className='AknImage-updater'
                />

                {null === previewImage && (
                    <div className='AknImage-uploader'>
                        <DefaultImage
                            src={defaultImageUrl}
                            alt={''}
                        />
                        <span className='AknImage-uploaderIllustration' />
                        <span className='AknImage-uploaderHelper'>
                            <Translate id={'pim_apps.edit_image.upload_helper'} />{' '}
                            <HelperLink href='#'>
                                <Translate id={'pim_apps.edit_image.click_here'} />
                            </HelperLink>.
                        </span>
                    </div>
                )}
                {null !== previewImage && (
                    <>
                        <div className='AknImage-action'>
                            <span className='AknImage-actionItem' onClick={() => onChange(null)}>
                                <Trash color='#ffffff' className='AknImage-actionItemIcon' />{' '}
                                <Translate id={'pim_apps.edit_image.remove_helper'} />
                            </span>
                        </div>
                        <div className='AknImage-displayContainer'>
                            <img
                                className='AknImage-display'
                                src={previewImage}
                                alt={''}
                            />
                        </div>
                    </>
                )}
            </div>
        </>
    );
};

export default ImageUploader;
