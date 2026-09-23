<?php

/**
 * 본문 이미지 한 장
 *
 * 이미지 블록·갤러리·작업 이미지가 함께 쓴다. 원본을 그대로 내보내면 4132px짜리를
 * 받아 706px로 그리게 된다. 본문에는 열 폭에 맞춰 줄인 WebP를 `srcset`으로 내주고,
 * 원본은 라이트박스가 볼 것으로 `data-full`에 따로 적는다.
 *
 * 원본 주소가 속성으로 HTML 안에 있어야 하는 이유: 정적 빌드는 HTML에 찍힌 `/media/`
 * 주소만 배포한다. 스크립트가 주소를 조립해 부르면 그 파일은 `dist/`에 없다.
 *
 * 근거: docs/adr/0018-responsive-images.md
 *
 * @var \Kirby\Cms\File $image
 * @var int $columns 본문 열 안에 나란히 놓이는 장수 (갤러리·작업 이미지)
 * @var bool $stack 좁은 화면에서 한 열로 쌓이는가 (갤러리)
 * @var \Kirby\Content\Field|string|null $alt
 */

$columns ??= 1;
$stack   ??= false;
$alt       = (string)($alt ?? $image->alt());

/**
 * 크기 계단. 본문 열은 좁은 화면 440px에서 1920px 화면 897px까지 변하고(ADR-0012),
 * 여기에 2~3배 밀도를 곱한 범위를 이웃 계단 사이가 1.33~2배가 되도록 덮는다.
 * 원본보다 큰 계단은 만들지 않는다 — 늘린 파일은 받을 이유가 없다. 대신 원본 폭을
 * 마지막 계단으로 넣어, 계단 사이에 걸린 원본도 제 해상도까지는 쓰이게 한다.
 */
$steps  = [480, 960, 1440, 1920];
$widths = array_values(array_filter($steps, fn ($w) => $w < $image->width()));

if ($image->width() <= end($steps)) {
    $widths[] = $image->width();
}

/**
 * GIF는 줄이지 않는다. GD가 움직이는 GIF의 첫 프레임만 남긴다.
 */
$resize = $image->extension() !== "gif" && $image->isResizable() === true;

$version = fn (int $w) => $image->thumb([
    "width"   => $w,
    "format"  => "webp",
    // 대부분이 글자가 있는 스크린샷이다. 이보다 낮추면 획 가장자리가 번진다
    "quality" => 85,
]);

/**
 * `sizes`는 --column(index.css)을 옮겨 적은 것이다. 한쪽을 고치면 다른 쪽도 고친다.
 *
 *   --column: min(100% - 40px, max(46.7vw, 360px + 16.7vw))
 *
 * 같은 식을 min()/max()로 그대로 쓰지 않고 구간으로 풀었다. 세 구간의 경계는
 * ADR-0012가 계산한 480px과 1200px이다. `sizes` 안의 min()/max()는 브라우저 지원이
 * 고르지 않고, 못 읽으면 100vw로 떨어져 가장 큰 파일을 받는다 — 조용히 효과만 사라진다.
 * 여기의 분기는 받을 파일을 고르는 힌트일 뿐이라 화면 표시는 여전히 연속이다.
 */
$gap  = ($columns - 1) * 16;
$part = fn (string $column) => $columns === 1
    ? "calc({$column})"
    : "calc(({$column} - {$gap}px) / {$columns})";

$sizes = implode(", ", [
    "(max-width: 480px) " . ($stack ? "calc(100vw - 40px)" : $part("100vw - 40px")),
    "(max-width: 1200px) " . $part("360px + 16.7vw"),
    $part("46.7vw"),
]);

$src = $image->url();
$srcset = null;

if ($resize === true) {
    // 브라우저가 srcset을 안 읽을 때 받는 파일. 넓은 화면 1배 밀도의 열 폭에 가깝다
    $fallback = current(array_filter($widths, fn ($w) => $w >= 960)) ?: end($widths);
    $src = $version($fallback)->url();
    $srcset = implode(", ", array_map(fn ($w) => $version($w)->url() . " {$w}w", $widths));
}

echo Html::img($src, [
    "srcset"    => $srcset,
    "sizes"     => $srcset === null ? null : $sizes,
    // 비율을 먼저 알려 둔다. lazy로 늦게 오는 이미지가 도착하며 글을 밀어내지 않는다
    "width"     => $image->width(),
    "height"    => $image->height(),
    "alt"       => $alt,
    "loading"   => "lazy",
    "decoding"  => "async",
    "data-full" => $image->url(),
]);
