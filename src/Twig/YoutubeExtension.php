<?php

namespace Styleflasher\eZPlatformBaseBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class YoutubeExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('youtube', [$this, 'youtubeFilter']),
            new TwigFilter('youtubeid', [$this, 'youtubeIdFilter'])
        ];
    }

    public function youtubeFilter($url, $autoplay = 0, $autohide = 1, $controls = 1, $showinfo = 0)
    {
        $youtubeId = $this->youtubeIdFilter($url);
        return "https://www.youtube.com/embed/".$youtubeId."?autoplay=".
            $autoplay."&controls=".$controls;
    }

    public function youtubeIdFilter($url)
    {
        $parts = parse_url($url);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $qs);
            if (isset($qs['v'])) {
                return $qs['v'];
            }
            if (isset($qs['vi'])) {
                return $qs['vi'];
            }
        }
        if (isset($parts['path'])) {
            $path = explode('/', trim($parts['path'], '/'));
            return $path[count($path)-1];
        }
        return false;
    }

    public function getName()
    {
        return 'youtube_extension';
    }
}
