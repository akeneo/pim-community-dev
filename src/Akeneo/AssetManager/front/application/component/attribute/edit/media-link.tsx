import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {MediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {Key} from 'akeneo-design-system';
import {
  createPrefixFromString,
  isValidPrefix,
  NormalizedPrefix,
  prefixStringValue,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';
import {
  createSuffixFromString,
  isValidSuffix,
  NormalizedSuffix,
  suffixStringValue,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {
  NormalizableAdditionalProperty,
  wrapNormalizableAdditionalProperty,
} from 'akeneoassetmanager/domain/model/attribute/attribute';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
import {MediaTypes, MediaType} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';

const MediaLinkView = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
  rights,
}: {
  attribute: MediaLinkAttribute;
  onAdditionalPropertyUpdated: (property: string, value: NormalizableAdditionalProperty) => void;
  onSubmit: () => void;
  errors: ValidationError[];
  rights: {
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
}) => {
  const inputTextClassName = `AknTextField AknTextField--light ${
    !rights.attribute.edit ? 'AknTextField--disabled' : ''
  }`;

  return (
    <React.Fragment>
      <div className="AknFieldContainer" data-code="prefix">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.prefix">
            {__('pim_asset_manager.attribute.edit.input.prefix')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_asset_manager.attribute.edit.input.prefix"
            name="prefix"
            readOnly={!rights.attribute.edit}
            value={prefixStringValue(attribute.prefix)}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!isValidPrefix(event.currentTarget.value)) {
                event.currentTarget.value = prefixStringValue(attribute.prefix);
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated(
                'prefix',
                wrapNormalizableAdditionalProperty<NormalizedPrefix>(
                  createPrefixFromString(event.currentTarget.value)
                ).normalize()
              );
            }}
          />
        </div>
        {getErrorsView(errors, 'prefix')}
      </div>
      <div className="AknFieldContainer" data-code="suffix">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.suffix">
            {__('pim_asset_manager.attribute.edit.input.suffix')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_asset_manager.attribute.edit.input.suffix"
            name="suffix"
            readOnly={!rights.attribute.edit}
            value={suffixStringValue(attribute.suffix)}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!isValidSuffix(event.currentTarget.value)) {
                event.currentTarget.value = suffixStringValue(attribute.suffix);
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated(
                'suffix',
                wrapNormalizableAdditionalProperty<NormalizedSuffix>(
                  createSuffixFromString(event.currentTarget.value)
                ).normalize()
              );
            }}
          />
        </div>
        {getErrorsView(errors, 'suffix')}
      </div>
      <div className="AknFieldContainer" data-code="mediaType">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.media_type">
            {__('pim_asset_manager.attribute.edit.input.media_type')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Select2
            id="pim_asset_manager.attribute.edit.input.media_type"
            name="media_type"
            data={(MediaTypes as any) as {[choiceValue: string]: string}}
            value={attribute.mediaType}
            readOnly={!rights.attribute.edit}
            configuration={{
              minimumResultsForSearch: Infinity,
            }}
            onChange={(mediaType: MediaType) => {
              onAdditionalPropertyUpdated('media_type', mediaType);
            }}
          />
        </div>
        {getErrorsView(errors, 'mediaType')}
      </div>
    </React.Fragment>
  );
};

export const view = MediaLinkView;
