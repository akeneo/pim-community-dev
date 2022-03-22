- SqlGetAllViewableLocalesForUser
- SqlGetAllEditableLocalesForUser
- GetAllActivatedLocales
  - Remove Activated column from the database ? since "activated" depends if the locale is linked to the channel
  - Maybe have 1 query that calculates all the results and have some other public api use this query and then recalculates in PHP the results
  - + cache
  - Returns the localeCode
- GetChannels
  - With "Channel" dedicated in the Service Readmodel: 
    - code
    - LocaleCodes
    - Labels
    - CategoryTreeCode
    - Add more if needed? 
- GetLocales
  - With "Locale" dedicated in the service read model:
    - code
    - labels
- isLocaleActivated($locale)
  - Possible to call "GetAllActivatedLocales" and filter
- FindActivatedCurrenciesInterface::ForChannel($channel): array<string>currencyCodes
- GetChannelCodeWithLocaleCodesInterface:
  - DO NOT ADD use `GetChannels` instead
  
- ChannelExistsWithLocaleInterface:
  - Either 1 class multiple methods OR Multiple queries with single responsibilities => TBD
    - isLocaleBoundToChannel(string $localeCode, string $channelCode): bool;
    - doesChannelExist(string $channelCode): bool;
    - isLocaleActive(string $localeCode): bool;


- IsCategoryTreeLinked => Should move into the Category domain later on. Not in the new service API

Questions:
=> Are channels & locales the same subdomain in the end ? with (Channel .. 1.* Locales)?
  -> [TargetMarket|Channel|Market]?/src/Domain/{Locale,Channel,Market?}
=> Should we introduce VO for read model properties ?
  -> DTO vs aggregate: no need ?
  -> to validate with other pple
