<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Str;

/**
 * 페이지 메타데이터
 *
 * <title>, meta description, Open Graph에 쓸 값을 한곳에서 만든다.
 * 템플릿은 제목을 넘기지 않는다 — header.php가 이 메서드들을 직접 읽는다.
 * 근거: docs/adr/0005-page-metadata.md
 */

/** description 최대 길이. 검색 결과에서 잘리지 않는 상한. */
const SEO_DESCRIPTION_LENGTH = 160;

/** 요약을 자동 추출할 때 본문으로 인정하는 블록 타입. */
const SEO_PROSE_BLOCKS = ["markdown", "text"];

/** 소셜 카드 규격. summary_large_image가 기대하는 1.91:1. */
const SEO_CARD_WIDTH = 1200;
const SEO_CARD_HEIGHT = 630;

/**
 * 카드의 원본으로 삼을 확장자. svg 같은 벡터는 GD가 다루지 못해 제외한다.
 *
 * 결과물은 확장자와 무관하게 늘 jpg다(SEO_CARD_OUTPUT) — avif·webp는
 * 크롤러 지원이 고르지 않고, jpg는 어디서나 렌더된다.
 */
const SEO_CARD_FORMATS = ["jpg", "jpeg", "png", "webp", "avif", "gif"];
const SEO_CARD_OUTPUT = "jpg";

/**
 * 이미지가 없는 페이지가 물려받는 기본 카드. 1200×630으로 만들어 둔다.
 *
 * 둘 다 없으면 og:image를 아예 내보내지 않는다 — 없는 파일을 가리키는
 * 태그보다 태그가 없는 편이 낫다.
 */
const SEO_CARD_FALLBACKS = ["assets/og-default.png", "assets/og-default.jpg"];

App::plugin("massivevoid/seo", [
    "pageMethods" => [
        /**
         * 검색 결과와 브라우저 탭에 쓰는 제목.
         *
         * 홈은 사이트 이름 자체가 제목이므로 접미사를 붙이지 않는다.
         */
        "metaTitle" => function (): string {
            if ($this->isHomePage() === true) {
                return $this
                    ->site()
                    ->displayTitle()
                    ->or($this->site()->title())
                    ->value();
            }

            return $this->title() . " | " . $this->site()->title();
        },

        /**
         * 검색 결과와 공유 카드에 쓰는 요약.
         *
         * 순서대로 찾는다: description 필드 → 본문 첫 문단 → 사이트 기본 요약.
         * 본문이 없는 페이지(홈)나 아직 요약을 쓰지 않은 글도 빈 값이 되지 않는다.
         */
        "metaDescription" => function (): string {
            if ($this->description()->isNotEmpty() === true) {
                return $this->description()->value();
            }

            if ($excerpt = $this->metaExcerpt()) {
                return $excerpt;
            }

            return $this->site()->description()->value() ?? "";
        },

        /**
         * 본문 첫 문단에서 뽑은 요약. 뽑을 것이 없으면 null.
         *
         * 소제목은 요약이 아니므로 heading을 걷어낸 뒤 남는 산문을 쓴다.
         */
        "metaExcerpt" => function (): ?string {
            foreach ($this->blocks()->toBlocks() as $block) {
                if (in_array($block->type(), SEO_PROSE_BLOCKS, true) !== true) {
                    continue;
                }

                $html = preg_replace(
                    "!<h[1-6][^>]*>.*?</h[1-6]>!is",
                    "",
                    $block->toHtml(),
                );
                $text = html_entity_decode(
                    strip_tags($html),
                    ENT_QUOTES | ENT_HTML5,
                    "UTF-8",
                );
                $text = trim(preg_replace('/\s+/u', " ", $text));

                if ($text !== "") {
                    return Str::excerpt($text, SEO_DESCRIPTION_LENGTH);
                }
            }

            return null;
        },

        /**
         * 카드에 쓸 원본 이미지. 없으면 null.
         *
         * 본문 첫 image 블록을 먼저 본다. 블록은 파일을 uuid로 참조하므로
         * 이미지가 다른 페이지에 저장돼 있어도 찾아낸다 — about-mclaren이
         * 그런 경우다. 블록에 없으면 페이지가 가진 첫 이미지를 쓴다(프로젝트).
         */
        "metaImageFile" => function (): ?\Kirby\Cms\File {
            foreach ($this->blocks()->toBlocks() as $block) {
                if ($block->type() !== "image") {
                    continue;
                }

                if ($file = $block->image()->toFile()) {
                    return $file;
                }
            }

            return $this->image();
        },

        /**
         * 소셜 카드. url·width·height·alt를 담은 배열이거나 null.
         *
         * 원본을 1.91:1로 잘라 쓰고, 쓸 이미지가 없으면 기본 카드로 메운다.
         * 기본 카드 파일마저 없으면 null — 없는 파일을 가리키는 og:image를
         * 내보내는 것보다 태그가 없는 편이 낫다.
         */
        "metaCard" => function (): ?array {
            $file = $this->metaImageFile();

            if (
                $file !== null &&
                in_array($file->extension(), SEO_CARD_FORMATS, true) === true
            ) {
                /**
                 * 썸네일 생성은 서버의 GD가 원본 포맷을 읽을 수 있어야 한다.
                 * avif가 대표적으로 위험하다 — 못 읽으면 예외가 나는데,
                 * 그게 header.php에서 터지면 페이지 전체가 500이 된다.
                 * 카드 하나 때문에 글을 못 읽게 되는 것보다 기본 카드가 낫다.
                 */
                try {
                    /**
                     * crop()이 아니라 thumb()을 쓴다 — crop()은 options에서
                     * quality와 crop만 읽고 format을 버린다(FileModifications).
                     * 그러면 avif 원본이 avif 카드로 나가고, 크롤러가 못 읽는다.
                     */
                    $card = $file->thumb([
                        "width" => SEO_CARD_WIDTH,
                        "height" => SEO_CARD_HEIGHT,
                        "crop" => true,
                        "format" => SEO_CARD_OUTPUT,
                    ]);

                    return [
                        "url" => $card->url(),
                        "width" => $card->width(),
                        "height" => $card->height(),
                        // 대체 텍스트에는 사이트 접미사를 붙이지 않는다
                        "alt" => $file->alt()->or($this->title())->value(),
                    ];
                } catch (\Throwable) {
                    // 아래 기본 카드로 내려간다
                }
            }

            foreach (SEO_CARD_FALLBACKS as $path) {
                $root = kirby()->root("index") . "/" . $path;

                if (file_exists($root) === true) {
                    $fallback = ["path" => $path, "root" => $root];
                    break;
                }
            }

            if (isset($fallback) !== true) {
                return null;
            }

            [$width, $height] = getimagesize($fallback["root"]);

            return [
                "url" => url($fallback["path"]),
                "width" => $width,
                "height" => $height,
                "alt" => $this->site()->title()->value(),
            ];
        },

        /**
         * og:type — 한 편의 글로 읽히는 페이지만 article이다.
         */
        "metaType" => function (): string {
            return in_array(
                $this->intendedTemplate()->name(),
                ["blogpost", "project"],
                true,
            )
                ? "article"
                : "website";
        },
    ],
]);
