<?php

namespace Styleflasher\eZPlatformBaseBundle\Tests\Services\Twig;

use PHPUnit\Framework\TestCase;
use Styleflasher\eZPlatformBaseBundle\Twig\YoutubeExtension;

/*
 *
 * vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php src/Tests/Services/Twig/YoutubeExtensionTest.php
 */

class YoutubeExtensionTest extends TestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testYoutubeFilter($url, $expectedUrl)
    {
        $youtubeExtension = new YoutubeExtension();
        $iframeUrl = $youtubeExtension->youtubeFilter(
            $url,
            0,
            1,
            1,
            0
        );

        $this->assertEquals($iframeUrl, $expectedUrl);
    }

    public function urlProvider()
    {
        return [
            [
                'https://www.youtube.com/watch?v=Mzg8QJJTix',
                'https://youtube.com/embed/Mzg8QJJTix?autoplay=0&controls=1&showinfo=0&autohide=1'
            ],
            [
                'https://youtu.be/oC1nCKGDSes',
                'https://youtube.com/embed/oC1nCKGDSes?autoplay=0&controls=1&showinfo=0&autohide=1'
            ],
            [
                'https://www.youtube.com/watch?v=oC1nCKGDSes&feature=youtu.be',
                'https://youtube.com/embed/oC1nCKGDSes?autoplay=0&controls=1&showinfo=0&autohide=1'
            ]
        ];
    }
}
