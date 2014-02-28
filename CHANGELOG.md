Changelog
=========

* **2014-02-28**: Updated the create commit for the create directory
  bergie/create.git to the latest (a148ce9633535930d7b4b70cc1088102f5c5eb90)

* **2013-12-26**: 1.0 allowed everybody to edit content if there was no
  firewall configured on a route. This version is more secure, preventing
  editing if there is no firewall configured. If you want to allow everybody
  to edit content, set `cmf_create.role: false`.
  If you use this together with the MediaBundle, be sure to use at least 1.1.0
  of MediaBundle or image upload will no longer be allowed.

1.0.0-RC2
---------

* **2013-10-02**: now requires MediaBundle 1.0.0-RC2 which added `UploadFileHelperInterface`

1.0.0-RC1
---------

* **2013-09-10**: changed the default setting for the `role` option to ROLE_ADMIN.
  You hopefully already configure this option. If you really want a public
  writable page set the config option `cmf_create.role` to IS_AUTHENTICATED_ANONYMOUSLY
* **2013-09-04**: make CKEditor the default

1.0.0-beta4
-----------

* **2013-08-20**: Changed configuration to match Bundle standards
* **2013-08-16**: [Model] moved Image document, interface and logic to CmfMedia
  , the `image.static_basepath` configuration is renamed to `image.basepath`

1.0.0-beta3
-----------

* **2013-07-28**: [DependencyInjection] added `enabled` flag to `image` config
