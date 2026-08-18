<?php

use Kirby\Cms\App;

/**
 * 자산 캐시 무효화
 *
 * 스타일시트 URL에 파일 수정시각을 붙인다. 내용이 바뀌면 URL이 바뀌므로,
 * 브라우저가 캐시에 있는 낡은 스타일시트를 계속 쓸 수 없다.
 * 근거: docs/adr/0010-asset-cache-busting.md
 */

/**
 * URL에 그 파일의 수정시각을 쿼리로 붙인다.
 *
 * 이 서버에 실물이 없는 주소(외부 CDN 등)는 손대지 않고 그대로 돌려준다.
 */
function assetVersionedUrl(App $kirby, string $url): string
{
    // 쿼리가 이미 붙어 있으면 부른 쪽의 의도를 존중한다
    if (str_contains($url, "?") === true) {
        return $url;
    }

    // 상대 경로("assets/css/index.css")와 절대 URL(`@auto`가 만드는 값)이
    // 둘 다 들어온다. 경로만 떼어내면 한 방식으로 다룰 수 있다.
    $path = ltrim(parse_url($url, PHP_URL_PATH) ?: "", "/");
    $file = $kirby->root("index") . "/" . $path;

    if (is_file($file) === false) {
        return $url;
    }

    return $kirby->url("index") . "/" . $path . "?v=" . filemtime($file);
}

/**
 * css 컴포넌트는 `css()` 헬퍼가 URL을 뱉기 직전에 거치는 자리다.
 * 여기 걸어두면 템플릿의 `@auto`까지 한곳에서 처리된다 —
 * 헬퍼가 `@auto`를 실제 경로로 바꾼 **다음**에 이 컴포넌트를 부르기 때문이다.
 *
 * config.php가 아니라 플러그인인 것은 Kirby가 설정에서 읽는 확장이
 * api·routes·hooks 셋뿐이라서다. components는 플러그인으로만 등록된다.
 */
App::plugin("massivevoid/assets", [
    "components" => [
        "css" => function (
            App $kirby,
            string $url,
            $options = null,
        ): string {
            return assetVersionedUrl($kirby, $url);
        },
    ],
]);
