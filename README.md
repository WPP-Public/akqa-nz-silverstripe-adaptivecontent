# SilverStripe Adaptive Content

Generic sets of fields as data extensions. Originally intended to be the
back-end for an "adaptive" content strategy (content adapting to
device/context), but generally quite useful as a set of common, reusable fields.

## Installation (with composer)

	$   composer require heyday/silverstripe-adaptivecontent

## Usage

The main component of this module can be used simply by adding the
`AdaptiveContent` extension to a DataObject or Page instance:

```yaml
SomeDataObject:
  extensions:
    - Heyday\AdaptiveContent\Extensions\AdaptiveContent
```

The other field extensions work in the same way:

```yaml
SomePage:
  extensions:
    - Heyday\AdaptiveContent\Extensions\AdaptiveContent
    - Heyday\AdaptiveContent\Extensions\AdaptiveContentHierarchy
    - Heyday\AdaptiveContent\Extensions\AdaptiveContentRelated('Page')
```

## Example use

See [heyday/silverstripe-slices](https://github.com/heyday/silverstripe-slices)
for an example use of this module.

## Unit testing

```sh
composer install --dev
vendor/bin/phpunit
```
