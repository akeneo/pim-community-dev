import * as React from 'react';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import { getImageShowUrl } from 'akeneoenrichedentity/tools/media-url-generator';

export default ({
  enrichedEntity,
  locale,
  isLoading = false,
  onRedirectToEnrichedEntity,
}: {enrichedEntity: EnrichedEntity; locale: string; isLoading?:boolean, position: number} &
{
  onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => void;
}) => {
  return (
    <tr
      title={enrichedEntity.getLabel(locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${isLoading ? 'AknLoadingPlaceHolder' : ''}`}
      data-identifier={enrichedEntity.getIdentifier().stringValue()}
      onClick={() => onRedirectToEnrichedEntity(enrichedEntity)}
    >
      <td
        className="AknGrid-fullImage"
        style={{backgroundImage: `url("${getImageShowUrl(null, 'thumbnail')}")`}}
      />
      <td className="AknGrid-title">
        {enrichedEntity.getLabel(locale)}
      </td>
      <td className="AknGrid-subTitle">
        {enrichedEntity.getIdentifier().stringValue()}
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox" />
      <td className="AknGrid-bodyCell AknGrid-bodyCell--actions">
        <div className="AknButtonList AknButtonList--right" />
      </td>
    </tr>
  );
};

