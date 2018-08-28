import * as React from 'react';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import { getImageShowUrl } from 'akeneoenrichedentity/tools/media-url-generator';
const router = require('pim/router');

export default ({
  enrichedEntity,
  locale,
  isLoading = false,
  onRedirectToEnrichedEntity
}: {
  enrichedEntity: EnrichedEntity;
  locale: string;
  isLoading?: boolean;
  position: number;
} & {
  onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => void;
}) => {
  const path =
    "" !== enrichedEntity.getIdentifier().stringValue()
      ? `#${router.generate("akeneo_enriched_entities_enriched_entity_edit", {
          identifier: enrichedEntity.getIdentifier().stringValue()
        })}`
      : '';

  return (
    <a
      href={path}
      title={enrichedEntity.getLabel(locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? "AknLoadingPlaceHolder" : ""
      }`}
      data-identifier={enrichedEntity.getIdentifier().stringValue()}
      onClick={event => {
        event.preventDefault();

        onRedirectToEnrichedEntity(enrichedEntity);

        return false;
      }}
    >
      <span
        className="AknGrid-fullImage"
        style={{
          backgroundImage: `url("${getImageShowUrl(enrichedEntity.getImage(), "thumbnail")}")`
        }}
      />
      <span className="AknGrid-title">{enrichedEntity.getLabel(locale)}</span>
      <span className="AknGrid-subTitle">
        {enrichedEntity.getIdentifier().stringValue()}
      </span>
      <span className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox" />
      <span className="AknGrid-bodyCell AknGrid-bodyCell--actions">
        <div className="AknButtonList AknButtonList--right" />
      </span>
    </a>
  );
};
