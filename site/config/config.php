<?php
return [
    "debug" => true,
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
                return new \Kirby\Cms\Response($content, "application/xml");
            },
        ],
    ],
];
