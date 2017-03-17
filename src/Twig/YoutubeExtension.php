<?php
namespace Styleflasher\eZPlatformBaseBundle\Twig;

class YoutubeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('youtube', array($this, 'youtubeFilter'), array('is_safe'=>array('all'))),
            new \Twig_SimpleFilter('youtubeid', array($this, 'youtubeIdFilter'), array('is_safe'=>array('all'))),
        );
    }

    public function youtubeFilter($url, $autoplay = 0, $autohide = 1, $controls = 1, $showinfo = 0)
    {
        $youtubeId = $this->youtubeIdFilter($url);
        return "https://youtube.com/embed/".$youtubeId."?autoplay=".
            $autoplay."&controls=".$controls."&showinfo=".$showinfo."&autohide=".$autohide;
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
