# Useage

## Sujets
* Call the sujetcontroller from within your template
    `{{ render(controller('sf.standard.sujet.controller:renderSujetsAction', {
     locationId: location.id,
        template: 'components/sujet.html.twig'
        })) }}`

* add missing sujet alias to config.yml
    `
liip_imagine:
    filter_sets:
        sujet:
            filters:
                upscale: { min: [1920, 470] }
                thumbnail: { size: [1920, 470], mode: outbound }
`

## Search

Enable the routes of bundle in app/config/routing.yml:
`
styleflashersearch:
    resource: "@StyleflashereZPlatformBaseBundle/Resources/config/routing.yml"
`

Now you can access the route "/search". Use "q" as GET parameter. By default the search controller uses the line view templates.

The templates can be changed by changing the searchresult_view parameter:
`
system:
    site_group:
        search:
            searchresult_view: 'search'
`

## SEO

Insert the following snippet into you <head> tag:

```twig
{% if location is defined %}
    {{render(
        controller(
            "seofunctions:getseohead", {
                'locationid': location.id,
                'bundle': 'styleflasherezplatformbasebundle',
                'layout': 'seo'
            }
        )
    )}}
{% endif %}
```

Copy src/Resources/config/seo_settings.template.yml to app/config/seo_settings.yml. 

Import the file in your config.yml:

```yml
imports:
    - { resource: seo_settings.yml }
```

Modfiy your settings in seo_settings.yml for your needs.

Add the following fields to your content types (all fields are optional):

* meta_title (text line)
* meta_keywords (textline)
* meta_description (textline)
* meta_canonical_link (textline)
* meta_nofollow (checkbox)

If you have multiple languages, make shure that all languages are mentionend in the settings.
