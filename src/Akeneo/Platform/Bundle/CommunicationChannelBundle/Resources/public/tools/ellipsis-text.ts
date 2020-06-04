const ellipsisText = (text: string): string => {
  return text.split(/(?<=\.)/, 1)[0];
};

export {ellipsisText};
