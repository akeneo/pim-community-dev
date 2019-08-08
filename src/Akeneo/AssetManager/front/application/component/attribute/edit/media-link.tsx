import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {MediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import Key from 'akeneoassetmanager/tools/key';
import {
  prefixStringValue,
  createPrefixFromString,
  isValidPrefix,
  NormalizedPrefix,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';
import {
  suffixStringValue,
  isValidSuffix,
  createSuffixFromString,
  NormalizedSuffix,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {
  wrapNormalizableAdditionalProperty,
  NormalizableAdditionalProperty,
} from 'akeneoassetmanager/domain/model/attribute/attribute';

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
                wrapNormalizableAdditionalProperty<NormalizedPrefix>(createPrefixFromString(event.currentTarget.value))
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
                wrapNormalizableAdditionalProperty<NormalizedSuffix>(createSuffixFromString(event.currentTarget.value))
              );
            }}
          />
        </div>
        {getErrorsView(errors, 'suffix')}
      </div>
    </React.Fragment>
  );
};

export const view = MediaLinkView;
