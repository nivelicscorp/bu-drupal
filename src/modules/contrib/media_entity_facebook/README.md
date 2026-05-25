# Introduction

The Media Entity Facebook module provides the ability to embed facebook
posts (including images and videos) using the Drupal's media module.

More specifically, it provides a media source plugin, allowing site
builders to create a new "Facebook" media type where content editors
can paste in Facebook embeds and post URLs to create re-usable media
entities representing individual Facebook posts.

This module is very similar to Media Entity Instagram.

# Requirements

This module only requires core's Media module.

# Installation

Download and install the module as you would with any other Drupal module:

* Download this module and move the folder it the DRUPAL_ROOT/modules
  directory. Using composer to download modules is the best practice.
* Enable the module in your Drupal admin interface.

# Configuration

If you wish tio use Facebook's Oembed API instead of the Embedded Posts,
then visit /admin/config/media/facebook-settings and provide a Facebook app
ID and secret. Please keep in mind that API can be used only after
Facebook app review. It is not required for this module anymore.
But for legacy sites it is still available, although not as the default.

After providing that information, create a new "Facebook" media type and
select "Facebook" as the source provider.

You can then create new Facebook media entities and paste in either the
embed code or the Facebook post URL in the source field.
