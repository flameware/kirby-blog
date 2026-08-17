<?php
/**
 * www 호스트로 들어온 요청에도 운영 설정을 그대로 적용한다.
 *
 * Kirby는 `config.{호스트}.php`를 호스트명으로 정확히 매칭하므로(Environment::options),
 * 이 파일이 없으면 www 요청에는 운영 설정이 하나도 로드되지 않는다.
 * 그 상태에서는 Panel이 켜진 채 노출되고, canonical과 사이트맵이 www로 찍힌다.
 *
 * 정상 경로에서는 서버가 www를 apex로 301 넘기므로 이 파일까지 오지 않는다.
 * 서버 설정이 재설치·인증서 재발급 과정에서 조용히 되돌아갈 때를 위한 두 번째 방어선이다.
 *
 * 근거: docs/adr/0003-canonical-host.md
 */
return require __DIR__ . "/config.massivevoid.com.php";
