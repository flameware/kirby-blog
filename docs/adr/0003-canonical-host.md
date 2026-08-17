# ADR-0003: 정본 호스트는 서버 리다이렉트와 Kirby `url` 옵션으로 이중 고정한다

- **상태:** 채택
- **날짜:** 2026-08-17

## 맥락

Google Search Console이 "Alternate page with proper canonical tag" 알림을 보내와 색인 상태를 전수 확인한 결과, 미색인 14건 중 **11건이 `www.massivevoid.com` 호스트**였다(중복 canonical 3건 + 크롤 후 미색인 8건). 정작 알림이 온 1건(`/?ref=www.sohnseongki.com`)은 canonical이 제대로 동작한 정상 사례였다.

원인은 세 겹이 겹친 것이다:

- `www`는 apex와 같은 IP를 가리키고, Apache에 활성화된 vhost가 하나뿐이라 **어떤 Host 헤더로 와도 같은 사이트가 200으로 응답**했다.
- certbot을 `-d massivevoid.com`으로만 돌린 탓에 certbot이 넣은 http→https 리다이렉트 룰이 `%{SERVER_NAME} = massivevoid.com` 조건부였다. **`www`는 이 조건에 걸리지 않아 301을 받지 못했다.**
- Kirby는 `url` 옵션이 없으면 **요청 호스트로 URL을 생성한다**(`Kirby\Http\Environment`). 그래서 www로 들어온 요청이 canonical도, `sitemap.xml`도 www로 찍어냈다.

결과적으로 사이트 전체가 www에 복제되었고, **그 복제본이 스스로를 정본이라 선언**했다. 구글은 이를 거부하고 apex를 정본으로 골랐다(= "Duplicate, Google chose different canonical than user"). 덤으로 호스트별 설정 파일(`config.massivevoid.com.php`)이 www에는 로드되지 않아, www 쪽에서는 `panel => false`도 애널리틱스 설정도 적용되지 않고 있었다.

## 결정

**정본 호스트는 `https://massivevoid.com` 하나이며, 이를 서버와 애플리케이션 두 곳에서 각각 강제한다.**

1. **서버(1차 방어선).** vhost에 `ServerAlias www.massivevoid.com`을 추가하고 certbot을 `-d massivevoid.com -d www.massivevoid.com`으로 재발급한 뒤, www로 오는 모든 요청을 `https://massivevoid.com`으로 301 넘긴다. 정상 상태에서 www 요청은 PHP까지 도달하지 않는다.
2. **애플리케이션(2차 방어선).** `site/config/config.massivevoid.com.php`에 `"url" => "https://massivevoid.com"`을 못 박는다. Kirby는 허용 URL이 하나면 요청 호스트를 무시하고 그 값을 베이스로 고정한다. 나아가 `config.www.massivevoid.com.php`가 apex 설정을 그대로 `require` 하여, **www 호스트로 요청이 닿더라도** 정본 URL·Panel 비활성화·애널리틱스가 동일하게 적용되게 한다.

로컬 개발 환경은 영향을 받지 않는다. 호스트가 `localhost`면 두 파일 모두 로드되지 않아 URL이 자동 감지된다.

## 근거

**서버 리다이렉트만으로 끝내지 않은 이유:** 이번 사고의 본질은 정본 호스트가 서버 설정 한 줄에만 의존하고 있었다는 것이다. 그 한 줄은 애초에 사람이 넣은 것도 아니고 certbot이 자동 생성한 조건부 룰이었으며, 조건에서 www가 빠진 것을 아무도 알아채지 못한 채 1년 가까이 굴러갔다. 서버 설정은 재설치·인증서 재발급 과정에서 조용히 되돌아갈 수 있지만 저장소는 그렇지 않다. 앱 쪽 고정은 그때도 살아남는다.

**`config.php`에 전역으로 박지 않은 이유:** 그러면 로컬 개발에서 모든 링크와 애셋 경로가 운영 도메인을 가리킨다. 호스트별 설정에 두면 개발 환경은 자동 감지 그대로 남는다.

**`config.www.massivevoid.com.php`를 별도로 만든 이유:** Kirby는 호스트명을 정확히 매칭해 `config.{호스트}.php`를 찾는다(`Environment::options`). apex 파일 하나만으로는 www 요청에 **아무 설정도** 로드되지 않아, 2차 방어선이 정작 필요한 상황에서 작동하지 않는다. 한 줄짜리 `require`가 정본 URL과 Panel 노출 차단을 동시에 메운다.

**변형 URL을 리다이렉트로 정리하지 않는 이유:** 끝 슬래시(`/blog/글/`), 대소문자(`/HOME`), 쿼리 파라미터(`?ref=`)는 여전히 200으로 응답하며 canonical로만 정본을 가리킨다. 구글이 이 방식을 제대로 처리하고 있음을 알림 자체가 확인해 주었으므로, 리다이렉트 룰을 늘려 얻을 이득이 없다.

## 결과

- **인증서 갱신 대상이 두 도메인으로 늘었다.** 이후 certbot 재발급·마이그레이션 시 `www`를 빠뜨리면 `https://www`가 다시 TLS 오류를 낸다.
- **운영 URL이 저장소에 하드코딩되었다.** 도메인을 바꾸는 날에는 `config.massivevoid.com.php`의 `url`, 파일명 두 개, 그리고 서버 vhost를 함께 고쳐야 한다.
- 구글이 8건을 마지막으로 크롤한 것이 2~3월이므로, GSC 검증을 시작해도 반영까지 몇 주가 걸릴 수 있다.
