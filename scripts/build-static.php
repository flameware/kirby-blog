<?php

/**
 * 정적 빌드
 *
 * Kirby로 모든 화면을 한 번씩 그려 `dist/`에 HTML로 떨군다. 운영에는 PHP가 없고,
 * 이 스크립트가 도는 곳(로컬, CI)이 곧 "서버"다.
 *
 *   php scripts/build-static.php            # dist/ 에 빌드
 *   php scripts/build-static.php out-dir    # 다른 폴더에 빌드
 *
 * 하는 일은 넷이다.
 *   1. 운영 설정으로 Kirby를 띄운다 (정본 주소, 애널리틱스)
 *   2. 발행된 모든 화면 + 설정의 라우트(sitemap.xml) + 404를 그린다
 *   3. 그린 HTML이 가리키는 /media/ 파일을 실제로 만들어 옮긴다
 *   4. assets/ 와 static/ (_headers 등)을 옮긴다
 *
 * 근거: docs/adr/0016-static-build.md
 */

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

$root = dirname(__DIR__);
$dist = $root . "/" . trim($argv[1] ?? "dist", "/");

require $root . "/kirby/bootstrap.php";

/**
 * 1. 운영 설정
 *
 * Kirby는 호스트 이름으로 `config.massivevoid.com.php`를 고르는데, CLI에는 호스트가 없다.
 * 그래서 그 파일을 직접 읽어 넘긴다 — 운영 설정의 출처는 여전히 한 곳이다.
 */
$production = require $root . "/site/config/config.massivevoid.com.php";

$kirby = new App([
    "roots" => ["index" => $root],
    "options" => $production + [
        // 미디어 주소의 해시는 기본적으로 **파일의 절대 경로**를 소금으로 쓴다.
        // 그대로 두면 빌드하는 기계마다 이미지 주소가 달라지고, 주소가 달라지면
        // 1년짜리 immutable 캐시가 통째로 버려진다. 소금을 고정해 어디서 빌드해도
        // 같은 주소가 나오게 한다. 정적 사이트에는 초안 파일이 배포되지 않으므로
        // 이 값이 공개되어도 지킬 것이 없다.
        "content" => ["salt" => "massivevoid"],
    ],
]);

$base = rtrim($kirby->url("index"), "/");

if ($base !== "https://massivevoid.com") {
    fwrite(STDERR, "정본 주소가 아니다: {$base}\n");
    exit(1);
}

Dir::remove($dist);
Dir::make($dist);

$written = [];
$html = [];

$write = function (string $path, string $body) use ($dist, &$written) {
    F::write($dist . "/" . $path, $body);
    $written[] = $path;
};

/**
 * 2. 화면
 *
 * `index()`는 초안을 포함하지 않는다. 목록에 없는(unlisted) 화면은 포함된다 —
 * 지금 운영 서버에서도 주소를 알면 열리므로 같은 동작이다.
 */
$kirby->impersonate("nobody");

foreach ($kirby->site()->index() as $page) {
    if ($page->isErrorPage() === true) {
        continue;
    }

    $kirby->site()->visit($page);
    $body = $page->render();

    // `blog/index.html`이 아니라 `blog.html`이다. Cloudflare Pages는 앞의 것을 `/blog/`로,
    // 뒤의 것을 `/blog`로 내준다 — canonical과 사이트맵이 슬래시 없는 주소를 쓰므로
    // 뒤쪽이어야 정본 주소가 리다이렉트 없이 200으로 열린다.
    $path = $page->isHomePage() ? "index.html" : $page->uri() . ".html";

    $write($path, $body);
    $html[] = $body;
}

// 404 — Cloudflare Pages는 루트의 404.html을 없는 주소에 돌려준다
$error = $kirby->site()->errorPage();
$kirby->site()->visit($error);
$body = $error->render();
$write("404.html", $body);
$html[] = $body;

// 설정에 등록된 라우트 중 파일로 떨굴 것
foreach (["sitemap.xml"] as $route) {
    $response = $kirby->call($route);
    $write($route, $response->body());
}

/**
 * 3. 미디어
 *
 * Kirby는 이미지를 그릴 때 파일을 만들지 않는다. `media/` 주소만 찍어 두고,
 * 그 주소로 첫 요청이 올 때 원본을 복사하거나 썸네일을 굽는다. 정적 사이트에는
 * 그 "첫 요청"이 없으므로 여기서 대신 한 번씩 불러 준다.
 */
preg_match_all(
    "#" . preg_quote($base, "#") . "/(media/[^\"'\\s)<>]+)#",
    implode("\n", $html),
    $matches,
);

$media = array_unique(array_map("html_entity_decode", $matches[1]));
$missing = [];

foreach ($media as $path) {
    $path = rawurldecode(strtok($path, "?"));
    $kirby->call($path);

    $source = $root . "/" . $path;

    if (is_file($source) === false) {
        $missing[] = $path;
        continue;
    }

    // 심볼릭 링크로 발행된 원본도 실제 파일로 옮긴다
    Dir::make(dirname($dist . "/" . $path));
    copy(realpath($source), $dist . "/" . $path);
    $written[] = $path;
}

/**
 * 4. 자산과 호스팅 설정
 */
Dir::copy($root . "/assets", $dist . "/assets");

foreach (Dir::files($root . "/static", null, true) as $file) {
    copy($file, $dist . "/" . basename($file));
}

/**
 * 결과
 */
$pages = count($html);
$files = count($media) - count($missing);
echo "화면 {$pages}개 · 미디어 {$files}개 → {$dist}\n";

if ($missing !== []) {
    fwrite(STDERR, "만들지 못한 미디어:\n  " . implode("\n  ", $missing) . "\n");
    exit(1);
}
