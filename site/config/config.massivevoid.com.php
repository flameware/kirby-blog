<?php
return [
    "panel" => false, // 운영 서버에서는 Panel 비활성화

    // 정본 주소를 못 박는다. 이 값이 없으면 Kirby가 요청 호스트로 URL을 만들어서
    // www로 들어온 요청이 canonical과 사이트맵을 www로 찍어낸다(사이트 전체가 복제됨).
    // 근거: docs/adr/0003-canonical-host.md
    "url" => "https://massivevoid.com",

    // 애널리틱스는 운영 도메인에서만 동작 — 로컬 미리보기가 집계를 오염시키지 않도록
    // 근거: docs/adr/0001-analytics-goatcounter.md
    "analytics" => [
        "goatcounter" => "massivevoid",
    ],
];
