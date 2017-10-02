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

Now you can access the route "/serach". Use "q" as GET parameter.

