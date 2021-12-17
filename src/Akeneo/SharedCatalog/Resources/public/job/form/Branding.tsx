import React from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {DependenciesProvider, useTranslate} from '@akeneo-pim-community/legacy-bridge';
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

  return (
    <>
      <FieldContainer>
        <ImageUploader
          label={translate('shared_catalog.branding.upload.logo')}
          image={branding.image}
          validationErrors={validationErrors.filter(error => 'image' in error).map(error => translate(error.image))}
          onChange={image => onBrandingChange({...branding, image})}
        />
      </FieldContainer>
      <FieldContainer>
        <ImageUploader
          label={translate('shared_catalog.branding.upload.cover')}
          image={branding.cover_image ?? null}
          validationErrors={validationErrors.filter(error => 'cover_image' in error).map(error => translate(error.cover_image))}
          onChange={cover_image => onBrandingChange({...branding, cover_image})}
        />
      </FieldContainer>
      <FieldContainer>
        <Field label={translate('shared_catalog.branding.color')}>
          <ColorInput
            onChange={(color) => {console.log('call on branding change', color); onBrandingChange({...branding, color})}}
            value={branding.color ?? sharedCatalogsTheme.color.brand100}
          />
        </Field>
        {validationErrors.filter(error => 'color' in error).map(error => (
          <Helper>{translate(error.color)}</Helper>
        ))}
      </FieldContainer>
    </>
  );
};

export {Branding};
