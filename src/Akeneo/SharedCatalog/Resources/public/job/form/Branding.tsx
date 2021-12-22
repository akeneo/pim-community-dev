import React from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {useTranslate, useFeatureFlags} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ImageUploader} from './ImageUploader';
import {pimTheme, ColorInput, Field, Helper, sharedCatalogsTheme} from 'akeneo-design-system';

const FieldContainer = styled.div`
  margin-top: 40px;
`;

type Branding = {
  image: string | null;
  cover_image?: string | null;
  color?: string | null;
};

type BrandingError = {
  image: string;
  cover_image: string;
  color: string;
};

type BrandingProps = {
  branding: Branding;
  validationErrors: BrandingError[];
  onBrandingChange: (updatedBranding: Branding) => void;
};

const Branding = (props: BrandingProps) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>
      <BrandingForm {...props} />
    </ThemeProvider>
  </DependenciesProvider>
);

const BrandingForm = ({branding, validationErrors, onBrandingChange}: BrandingProps) => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();

  return (
    <>
      <FieldContainer>
        <ImageUploader
          label={translate('shared_catalog.branding.logo.label')}
          image={branding.image}
          validationErrors={validationErrors.filter(error => 'image' in error).map(error => translate(error.image))}
          onChange={image => onBrandingChange({...branding, image})}
        >
          <Helper>{translate('shared_catalog.branding.logo.file_information_helper')}</Helper>
        </ImageUploader>
      </FieldContainer>
      {isEnabled('new_shared_catalog_branding') && (
        <>
          <FieldContainer>
            <ImageUploader
              label={translate('shared_catalog.branding.cover.label')}
              image={branding.cover_image ?? null}
              validationErrors={validationErrors
                .filter(error => 'cover_image' in error)
                .map(error => translate(error.cover_image))}
              onChange={cover_image => onBrandingChange({...branding, cover_image})}
            >
              <Helper>{translate('shared_catalog.branding.cover.file_information_helper')}</Helper>
            </ImageUploader>
          </FieldContainer>
          <FieldContainer>
            <Field label={translate('shared_catalog.branding.color.label')}>
              <ColorInput
                onChange={color => onBrandingChange({...branding, color})}
                value={branding.color ?? sharedCatalogsTheme.color.brand100}
              />
              {validationErrors
                .filter(error => 'color' in error)
                .map(error => (
                  <Helper key={error.color} inline={true} level="error">
                    {translate(error.color)}
                  </Helper>
                ))}
            </Field>
          </FieldContainer>
        </>
      )}
    </>
  );
};

export {Branding};
