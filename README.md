# Multiple Attachments Metabox

Add multiple attachments metabox to a WordPress post type.

## Install

### Install Composer

You can download composer from [https://getcomposer.org/download/](https://getcomposer.org/download/).

### Setup your plugin

In your plugin's root folder:

Create `includes/vendor` directories if they are not exist.

add the following to the plugin `composer.json`

```json
"config": {
  "vendor-dir": "includes/vendor"
}
```

Then require the package

```shell
composer require mohjak/multiple-attachments-metabox
```

## Usage

```php
<?php

require_once('vendor/mohjak/multiple-attachments-meabox/class-multiple-attachments-metabox.php');
use Mohjak\WordPress\PostType\Metabox\MultipleAttachmentsMetabox;

function multipleUploads()
{
    $attachmentsMetabox = new MultipleAttachmentsMetabox([
        'metaboxPrefix' => 'multiple-attachments', // custom prefix for the metabox
        'screen' => 'post', // Or custom post type name
        'metaboxTitle' => __('Custom Attachments Metabox', Constants::TEXT_DOMAIN), // the metabox title
        'mediaType' => 'image', // Then image preview will show, otherwise, link to uploaded media
    ]);
}
add_action('admin_init', 'multipleUploads');
```

## CHANGELOG

[CHANGELOG](CHANGELOG)

## LICENSE

[GPL v3.0](LICENSE)
