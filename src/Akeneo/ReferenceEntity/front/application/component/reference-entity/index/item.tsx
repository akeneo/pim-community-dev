import * as React from 'react';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
const router = require('pim/router');

export default ({
  referenceEntity,
  locale,
  isLoading = false,
  onRedirectToReferenceEntity,
}: {
  referenceEntity: ReferenceEntity;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToReferenceEntity: (referenceEntity: ReferenceEntity) => void;
}) => {
  const path =
    '' !== referenceEntity.getIdentifier().stringValue()
      ? `#${router.generate('akeneo_reference_entities_reference_entity_edit', {
          identifier: referenceEntity.getIdentifier().stringValue(),
        })}`
      : '';

  return (
    <a
      href={path}
      title={referenceEntity.getLabel(locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? 'AknLoadingPlaceHolder' : ''
      }`}
      data-identifier={referenceEntity.getIdentifier().stringValue()}
      onClick={event => {
        event.preventDefault();

        onRedirectToReferenceEntity(referenceEntity);

        return false;
      }}
    >
      <span
        className="AknGrid-fullImage"
        style={{
          backgroundImage: `url("${getImageShowUrl(referenceEntity.getImage(), 'thumbnail')}")`,
        }}
      />
      <span className="AknGrid-title">{referenceEntity.getLabel(locale)}</span>
      <span className="AknGrid-subTitle">{referenceEntity.getIdentifier().stringValue()}</span>
      <span className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox" />
      <span className="AknGrid-bodyCell AknGrid-bodyCell--actions">
        <div className="AknButtonList AknButtonList--right AknButtonList--expanded" />
      </span>
    </a>
  );
};
