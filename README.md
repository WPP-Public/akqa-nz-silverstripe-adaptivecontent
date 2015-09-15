# SilverStripe Adaptive Content

Generic sets of fields as data extensions. Originally intended to be the back-end for an "adaptive" content strategy (content adapting to device/context), but generally quite useful as a set of common, reusable fields.

See [heyday/silverstripe-slices](https://github.com/heyday/silverstripe-slices) for an example implementation.

See `1.0` branch for SilverStripe 2.4 compatible version.

## Installation (with composer)

	$ composer require heyday/silverstripe-adaptivecontent:~3.0

## Usage

The main component of this module can be used simply by adding the `AdaptiveContent` extension to a dataobject or page:

```yaml
SomeDataObject:
  extensions:
    - AdaptiveContent
```

The other field extensions work in the same way:

```yaml
SomePage:
  extensions:
    - AdaptiveContent
    - AdaptiveContentHierarchy
    - AdaptiveContentRelated('Page')
```

## Unit testing

    $ composer install --dev
    $ vendor/bin/phpunit
