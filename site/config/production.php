<?php
/**
 * 운영 설정. `scripts/build-static.php`만 이 파일을 경로로 직접 읽는다.
 *
 * 이름이 Kirby의 자동 로드 패턴(`config.php`, `config.cli.php`, `config.{호스트}.php`,
 * `config.{서버주소}.php`)에 걸리지 않으므로 로컬 개발 서버나 다른 CLI 작업에는 새어 들지 않는다.
 */
return [
    // 정본 주소를 못 박는다. CLI 빌드에는 요청 호스트가 없으니, 이 값이 canonical과
    // 사이트맵에 찍히는 절대 주소의 유일한 출처다.
    // 근거: docs/adr/0003-canonical-host.md
    "url" => "https://massivevoid.com",

    // 애널리틱스는 운영 빌드에서만 켠다 — 로컬 미리보기가 집계를 오염시키지 않도록
    // 근거: docs/adr/0001-analytics-goatcounter.md
    "analytics" => [
        "goatcounter" => "massivevoid",
    ],
];
