<?php
return [
    "panel" => false, // 운영 서버에서는 Panel 비활성화

    // 애널리틱스는 운영 도메인에서만 동작 — 로컬 미리보기가 집계를 오염시키지 않도록
    // 근거: docs/adr/0001-analytics-goatcounter.md
    "analytics" => [
        "goatcounter" => "massivevoid",
    ],
];
