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
