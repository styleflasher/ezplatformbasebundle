<?php
namespace Styleflasher\eZPlatformBaseBundle\Twig;

class YoutubeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('youtube', array($this, 'youtubeFilter'), array('is_safe'=>array('all'))),
        );
    }

    public function youtubeFilter($url, $autoplay = 0, $autohide = 1, $controls = 1, $showinfo = 0)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars);
        $youtubeId = $my_array_of_vars['v'];
        return "https://youtube.com/embed/".$youtubeId."?autoplay=".
            $autoplay."&controls=".$controls."&showinfo=".$showinfo."&autohide=".$autohide;
    }

    public function getName()
    {
        return 'youtube_extension';
    }
}
