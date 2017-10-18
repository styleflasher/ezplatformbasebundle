<?php

namespace Styleflasher\eZPlatformBaseBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eZ\Bundle\EzPublishCoreBundle\Controller;

/**
 * DEPRECATED: USE https://github.com/Novactive/NovaeZSEOBundle
 * TODO: yml configs should be per siteaccess. add unit tests, refactor a lot.
 * @SuppressWarnings(PHPMD)
 */

class SEOController extends Controller
{

    protected $locationService;
    protected $contentService;
    protected $location;
    protected $content;
    protected $parentLocation;
    protected $parentContent;
    private $siteAccessLanguage;
    protected $rootNodeID;
    protected $standardString;
    /**
     * holds the word "page" or "Seite" when we have pagination
     */
    protected $pageString;
    protected $maxLengths;
    protected $keywordsMaxLength;
    protected $descriptionMaxLength;
    protected $keywordSpacer = ' ';
    protected $descriptionSpacer = ' - ';
    protected $titleSpacer = ' - ';
    protected $metaComponents;
    protected $seoObject;
    protected $metaType;
    protected $spacer;
    /**
     * @param unknown $locationID
     * @param unknown $bundle
     * @param unknown $layout
     *
     * @return unknown
     */
    public function getSEOHead($locationID, $bundle, $layout)
    {
        $seoObject = array(
            'title' => '',
            'keywords' => '',
            'description' => '',
            'canonical_link' => false,
            'canonical_extern' => false,
            'nofollow' => false,
            'hreflang' => false,
        );

        $this->seoObject = $seoObject;
        /*
         * Checken ob es eigene Settings für die Defaultsprache des Siteaccess gibt und checken ob eine Pagination auf
         * der Seite ist
         */

        if (isset($_GET['page']) && ($_GET['page'] != '')) {
            $page = $this->container->get('translator')->trans('seo.page').' '.$_GET['page'];
        } else {
            $page = false;
        }
        $this->pageString = $page;

        $seoSettings = $this->container->getParameter('seo');
        $siteaccessLanguage = $this->getRepository()
            ->getContentLanguageService()
            ->getDefaultLanguageCode();

        $this->siteAccessLanguage = $siteaccessLanguage;


        if (array_key_exists($siteaccessLanguage, $seoSettings)) {
            $seoConfig = $seoSettings[$siteaccessLanguage];
        } else {
            $seoConfig = $seoSettings['default'];
        }

        $this->metaComponents = $seoConfig['meta_components'];
        $this->standardString = $seoConfig['standardString'];

        /*
         * Location, Content und ggf. Parent fetchen
         */

        $location = $this->getRepository()
            ->getLocationService()
            ->loadLocation($locationID);
        $this->locationService= $this->getRepository()->getLocationService();
        $this->location = $location;

        $content = $this->getRepository()
            ->getContentService()
            ->loadContent($location->contentInfo->id);
        $this->contentService = $this->getRepository()->getContentService();
        $this->content = $content;

        if ($location->parentLocationId != 1) {
            $parentLocation = $this->getRepository()
                ->getLocationService()
                ->loadLocation($location->parentLocationId);

            $parentContent = $this->getRepository()
                ->getContentService()
                ->loadContent($parentLocation->contentInfo->id);
        } else {
            $parentLocation = false;
            $parentContent = false;
        }
        $this->parentLocation = $parentLocation;
        $this->parentContent = $parentContent;

        /*
         * Checken ob es das Objekt in der aktuellen Sprache gibt. Wenn es mehrere Sprachen gibt die nicht die aktuelle
         * sind diese für hreflang in ein Array eintragen
         */

        $langAvailable = false;
        foreach ($content->versionInfo->languageCodes as $language) {
            if ($language == $siteaccessLanguage) {
                $langAvailable = true;
            }
        }
        if ($seoSettings['general']['enable_hreflang']) {
            $seoObject['hreflang'] = $this->getHrefLang(
                $location,
                $content->versionInfo->languageCodes,
                $siteaccessLanguage,
                $langAvailable,
                $seoSettings
            );
        }

        $this->rootNodeID = $seoSettings['general']['rootNode'];
        $this->maxLengths = $seoSettings['general']['max_length'];

        /*
         * Checken ob Title, Keywords und Description gesetzt sind. Wenn sie gesetzt sind werden diese verwendet. Wenn
         * nicht werden sie zusammengebaut
         */

        if (array_key_exists('title_metatags', $content->fields) && $langAvailable &&
            ($content->getFieldValue('title_metatags', $siteaccessLanguage)->text != '')) {
            if ($page) {
                $seoObject['title'] = $content->getFieldValue('title_metatags', $siteaccessLanguage)->text.' '.$page;
            } else {
                $seoObject['title'] = $content->getFieldValue('title_metatags', $siteaccessLanguage)->text;
            }
        } else {
            $this->metaType = 'title';
            $this->spacer = ' - ';
            $seoObject['title'] = $this->buildTitle();
        }

        if (array_key_exists('keywords_metatags', $content->fields) && $langAvailable &&
            ($content->getFieldValue('keywords_metatags', $siteaccessLanguage)->text != '')) {
            if ($page) {
                $seoObject['keywords'] = $content->getFieldValue('keywords_metatags', $siteaccessLanguage)->text.' '.
                    $page;
            } else {
                $seoObject['keywords'] = $content->getFieldValue('keywords_metatags', $siteaccessLanguage)->text;
            }
        } else {
            $this->metaType = 'keywords';
            $this->spacer = ' ';
            $seoObject['keywords'] = $this->buildKeywordsOrDescription();
        }

        if (array_key_exists('description_metatags', $content->fields) && $langAvailable &&
            ($content->getFieldValue('description_metatags', $siteaccessLanguage)->text != '')) {
            if ($page) {
                $seoObject['description'] = $content->getFieldValue(
                    'description_metatags',
                    $siteaccessLanguage
                )->text.' '.$page;
            } else {
                $seoObject['description'] = $content->getFieldValue('description_metatags', $siteaccessLanguage)->text;
            }
        } else {
            $this->metaType = 'description';
            $this->spacer = ' - ';
            $seoObject['description'] = $this->buildKeywordsOrDescription();
        }

        /*
         * Checken ob der Canonical Link gesetzt ist. Wenn nicht checken ob die Location mehrere Orte hat.
         */

        if (array_key_exists('meta_canonical_link', $content->fields) && $langAvailable &&
            ($content->getFieldValue('meta_canonical_link', $siteaccessLanguage)->text != '')) {
            $seoObject['canonical_link'] = $content->getFieldValue('meta_canonical_link', $siteaccessLanguage)->text;
            $seoObject['canonical_extern'] = true;
        } else {
            $seoObject['canonical_link'] = $this->checkMultipleLocations($location);
        }

        /*
         * Checken ob es die Nofollow Checkbox gibt und ob diese markiert ist
         */

        if (array_key_exists('meta_nofollow', $content->fields) && $langAvailable &&
            ($content->getFieldValue('meta_nofollow', $siteaccessLanguage)->bool == true)) {
            $seoObject['nofollow'] = true;
        }

        $response = new Response();
        $response->setPublic();

        return $this->render(
            $bundle.'::'.$layout.'/seohead.html.twig',
            array(
                'title' => $seoObject['title'],
                'keywords' => $seoObject['keywords'],
                'description' => $seoObject['description'],
                'canonical_link' => $seoObject['canonical_link'],
                'canonical_extern' => $seoObject['canonical_extern'],
                'nofollow' => $seoObject['nofollow'],
                'hreflang' => $seoObject['hreflang'],
            ),
            $response
        );
    }

    protected function buildTitle()
    {
        $this->addWords($this->constructContentPageName());
        $this->addWords($this->constructParentPageName());
        $this->addWords($this->standardString);
        return $this->seoObject[$this->metaType];
    }

    /**
     * @param unknown $location
     * @param unknown $parentLocation
     * @param unknown $standardString
     * @param unknown $maxLength
     * @param unknown $pageRoot
     * @param unknown $component
     * @param unknown $spacer
     *
     * @return string
     */
    protected function buildKeywordsOrDescription()
    {
        $this->addWords($this->constructContentPageName());
        $this->addWords($this->constructParentPageName());
        $this->addWords($this->standardString);

        foreach ($this->metaComponents[$this->metaType] as $component) {
            $this->addWords($component);
        }
        return $this->seoObject[$this->metaType];
    }

    private function constructContentPageName()
    {
        if ($this->pageString) {
            return $this->getContentName().$this->spacer.$this->pageString;
        }
        return $this->getContentName();
    }

    private function constructParentPageName()
    {
        $parentLocation = $this->parentLocation;
        $rootNodeID = $this->rootNodeID;
        if ($parentLocation && ($parentLocation->contentInfo->mainLocationId != $rootNodeID)) {
            return $this->parentContent->versionInfo->names[$this->siteAccessLanguage];
        }
        return false;
    }

    protected function addWords($string)
    {
        $seoObject = $this->seoObject;
        $words = $seoObject[$this->metaType];
        if ((strlen($words) + strlen($string)) <= $this->maxLengths[$this->metaType]) {
            if ($words != '') {
                $string = $this->spacer.$string;
            }
            $seoObject[$this->metaType] .= $string;
        }
        $this->seoObject = $seoObject;
    }

    protected function getContentName()
    {
        $parentLocation = $this->parentLocation;
        $rootNodeID = $this->rootNodeID;
        //we don't want to show the name of the root node
        if ($parentLocation && ($parentLocation->contentInfo->mainLocationId != $rootNodeID)) {
            return $this->content->versionInfo->names[$this->siteAccessLanguage];
        }
        return false;
    }

    /**
     * @param unknown $location
     *
     * @return bool
     */
    protected function checkMultipleLocations($location)
    {
        if ($location->id == $location->contentInfo->mainLocationId) {
            return false;
        } else {
            $mainLocation = $this->getRepository()
                ->getLocationService()
                ->loadLocation($location->contentInfo->mainLocationId);
            $urlAlias = $this->getRepository()
                ->getURLAliasService()
                ->reverseLookup($mainLocation);

            return $urlAlias->path;
        }
    }

    /**
     * @param unknown $location
     * @param unknown $languages
     * @param unknown $siteaccessLanguage
     * @param unknown $langAvailable
     * @param unknown $seoSettings
     *
     * @return unknown
     */
    protected function getHrefLang($location, $languages, $siteaccessLanguage, $langAvailable, $seoSettings)
    {

        /*
         * Wenn die aktuelle Sprache verfügbar ist, diese nicht ins Array einfügen.
         * Weiters wird gechecked ob diese Sprache eine
         * eigene Domain hat. Wenn ja wird diese hinzugefügt.
         */
        foreach ($languages as $language) {
            $possibleLink = $this->getRepository()
                ->getURLAliasService()
                ->listLocationAliases($location, false, $language);
            if ($langAvailable && ($possibleLink[0]->languageCodes[0] != $siteaccessLanguage)) {
                if ($seoSettings[$language]['alternative-domain'] != false) {
                    $pathArray[$language]['alternativedomain'] = true;
                    $pathArray[$language]['sprachcode'] = $seoSettings[$language]['hreflang-sprachcode'];
                    $pathArray[$language]['link'] = $seoSettings[$language]['alternative-domain'].
                        $possibleLink[0]->path;
                } else {
                    $pathArray[$language]['alternativedomain'] = false;
                    $pathArray[$language]['sprachcode'] = $seoSettings[$language]['hreflang-sprachcode'];
                    $pathArray[$language]['link'] = $possibleLink[0]->path;
                }
            }
        }
        if (isset($pathArray) && (sizeof($pathArray) > 0)) {
            return $pathArray;
        } else {
            return false;
        }
    }
}
