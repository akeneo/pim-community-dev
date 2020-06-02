import {useState, useCallback, useEffect} from 'react';
import {Cards} from 'akeneocommunicationchannel/model/cards';
import {CardFetcher} from 'akeneocommunicationchannel/fetcher/cards';

const useCards = (cardFetcher: CardFetcher): {cards: Cards[] | null; fetchCards: () => Promise<void>} => {
  const [cards, setCards] = useState<Cards[] | null>(null);

  const fetchCards = useCallback(async () => {
    setCards(await cardFetcher.fetchAll());
  }, [setCards]);

  useEffect(() => {
    fetchCards();
  }, []);

  return {cards, fetchCards};
};

export {useCards};
