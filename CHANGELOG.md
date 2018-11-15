# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 4.2.0 - 2018-11-15

### Added

- [#4](https://github.com/netglue/prismic-php-kit/pull/4) Adds a new Exception `ExpiredPreviewTokenException` that is
thrown during `Api::previewSession()`

### Changed

- [#4](https://github.com/netglue/prismic-php-kit/pull/4) Changes behaviour of `Api::previewSession()` by throwing an
`ExpiredPreviewTokenException` in situations where a preview token has expired.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 4.1.0 - 2018-11-12

#### Fixed

- Nothing

#### Added

- Added method `void \Prismic\Api::reloadApiData()` to forcefully re-fetch the api data from the remote service. You
 would not normally need to call this in a standard web server environment because simply flushing the cache would be
 sufficient. However, in a cli environment handling multiple requests, flushing the cache will not free the 
 `\Prismic\ApiData` instance in memory, so you'd need to call this method during perhaps a web-hook triggered cache
 busting event… 
 
#### Deprecated

- Nothing

#### Removed

- Nothing

## 4.0.3 - 2018-10-18

#### Fixed

- Existing query strings are now preserved when setting the API Url. This is important if you are using integration fields
- Potential error fixed in `Api::previewSession()` where the link resolver could potentially be null

#### Added
- `Api::forms()` now returns a new class `SearchFormCollection` rather than a simple stdClass from json_decode. This improves type safety. 
- `Api::setRequestCookies(array) : void` allowing you to provide the request cookies to the Api instance manually for those situations where the $_COOKIE super global doesn't exist. 

#### Deprecated
- `Api::VERSION` is deprecated - it's not used anywhere and is pointless


## 4.0.2 - 2018-07-16

- Fixed preservation of line breaks when rich text does not contain spans   

## 4.0.1 - 2018-07-13

- Altered serialisation of HTML for structured text elements so that it preserves line breaks, replacing them with &lt;br /&gt;

## 4.0.0 - 2018-06-12

Initial release highlights include:

- \Psr\Cache is now used for caching
- Min version of PHP is now >= 7.1
- strict_types throughout with scalar type hints and return types
- Much improved test coverage ~ 95%
- Introduced document hydration so you can implement concrete content models in code
- Re-Introduced typed content fragments such as `RichText`, `Date`, `Embed`, `GeoPoint` etc.
- Transparent support for both V1 and V2 Apis
- Default document implementation can retrieve it's own relationships such as alternative translations
- Guaranteed to throw predictable exceptions all implementing the same interface in the same namespace

## Future changes

The `LinkResolver` as a concept is now pretty much a hard dependency for `\Prismic\Api` but relies on setter injection. The good thing is that this enables other elements, such as `RichText` to be serialised to HTML without needing to keep a link resolver handy all the time, i.e. `$textFragment->asHtml()` instead of `$textFragment->asHtml($linkResolver)` which can be annoying in views, but the bad thing is that it's a hidden dependency. At some point this needs to be made more friendly…

The same could be said of the `Hydrator` - The defaults are perfectly acceptable and they are straight-forward to override but it's still a hidden dependency.

In future, I'm considering providing ready to use factories and configuration suitable for `Psr\Container` - it's good to practice dependency injection and everyone's doing it right? 

### `\Prismic\Api` Notable Gotchas

- Methods `getByID`, `getByIDs`, `getByUID` have been camel-cased to `getById`, `getByIds` and `getByUid`
- Method `previewSession` signature has changed. A Link resolver is no longer required: `previewSession( string $token, string $defaultUrl) : string`
- Named constructor `get()` has changed to `Api::get( string $action, string $accessToken, Client $httpClient, \Psr\Cache\CacheItemPoolInterface $cache)
