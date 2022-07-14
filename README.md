# autoshortqr

This modules adds autocomputed short url and qr code fields to nodes and terms.

## Installation

Install the module using composer:
`composer require drupal/autoshortqr`

Enable the module using drush:
`drush en autoshortqr`

## Dependencies
The module requires drupal/barcodes and tecnickcom/tc-lib-barcode. For tecnickcom/tc-lib-barcode, the general packagist repo may be necessary.

## Setup

1. Navigate to the content type (/admin/structure/types/manage/TYPE) or vocabulary (/admin/structure/taxonomy/manage/TYPE).
2. In the section Autocode, activate the desired functions.
