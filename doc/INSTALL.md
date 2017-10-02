Installation instructions
=========================

Requirements
------------

* eZPublish 5 or eZ Platform 1.0+

Installation steps
------------------

### Use Composer

Run the following from your website root folder to install StyleflashereZPlatformBaseBundle:

```bash
$ composer require styleflasher/ezplatformbasebundle
```

### Activate the bundle

Activate required bundles in `app/AppKernel.php` file by adding them to the `$bundles` array in `registerBundles` method:

```php
public function registerBundles()
{
    $bundles = array(
    ...
        new Styleflasher\eZPlatformBaseBundle\StyleflashereZPlatformBaseBundle(),
    );
}
```

