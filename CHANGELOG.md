Changelog
=========

* **2013-09-10**: changed the default setting for the `role` option to ROLE_ADMIN.
  You hopefully already configure this option. If you really want a public
  writable page set the config option `cmf_create.role` to IS_AUTHENTICATED_ANONYMOUSLY

1.0.0-RC1
-----------

* **2013-09-04**: make CKEditor the default

1.0.0-beta4
-----------

* **2013-08-20**: Changed configuration to match Bundle standards
* **2013-08-16**: [Model] moved Image document, interface and logic to CmfMedia
  , the `image.static_basepath` configuration is renamed to `image.basepath`

1.0.0-beta3
-----------

* **2013-07-28**: [DependencyInjection] added `enabled` flag to `image` config
