const formatCampaign = (edition: string, version: string) => {
  if ('serenity' === edition.toLowerCase()) {
    return edition;
  }

  return `${edition}${version}`;
};

export {formatCampaign};
