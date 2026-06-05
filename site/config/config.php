<?php
return [
    "debug" => false,
    "panel" => [
        "install" => true,
    ],
    "routes" => [
        [
            "pattern" => "sitemap.xml",
            "action" => function () {
                $pages = site()
                    ->index()
                    ->filterBy("intendedTemplate", "not in", ["error"]);
                $content = snippet("sitemap", ["pages" => $pages], true);
                return new \Kirby\Cms\Response(
                    trim($content),
                    "application/xml",
                );
            },
        ],
    ],
];
